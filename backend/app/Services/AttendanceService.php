<?php

namespace App\Services;

use App\Exceptions\DomainError;
use App\Models\Attendance;
use App\Models\AttendanceEvent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Daily attendance with private photo storage and GPS metadata for each event. */
class AttendanceService
{
    public function __construct(private readonly AuditService $audit) {}

    public function today(User $user): array
    {
        $attendance = $this->forToday($user);

        return [
            'attendance_date' => now()->toDateString(),
            'checked_in' => $attendance?->check_in_at !== null,
            'checked_out' => $attendance?->check_out_at !== null,
            'check_in_at' => $attendance?->check_in_at?->toIso8601String(),
            'check_out_at' => $attendance?->check_out_at?->toIso8601String(),
        ];
    }

    public function checkIn(User $user, array $data): Attendance
    {
        return $this->recordEvent($user, $data, 'check_in');
    }

    public function checkOut(User $user, array $data): Attendance
    {
        return $this->recordEvent($user, $data, 'check_out');
    }

    private function recordEvent(User $user, array $data, string $eventType): Attendance
    {
        $this->assertWithinGeofence($data);

        /** @var UploadedFile $photo */
        $photo = $data['photo'];
        $path = $photo->store('attendance/'.$user->id.'/'.now()->toDateString(), 'local');
        if (! $path) {
            throw new \RuntimeException('Foto absensi gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($user, $data, $eventType, $path) {
                $now = now();
                if ($eventType === 'check_in') {
                    Attendance::query()->insertOrIgnore([[
                        'user_id' => $user->id,
                        'attendance_date' => $now->toDateString(),
                        'status' => 'present',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]]);
                }

                $attendance = Attendance::query()->where('user_id', $user->id)
                    ->whereDate('attendance_date', $now->toDateString())->lockForUpdate()->first();

                if (! $attendance) {
                    throw DomainError::invalidTransition('Absensi', 'belum_check_in', 'check_out');
                }
                $timestampColumn = $eventType === 'check_in' ? 'check_in_at' : 'check_out_at';
                if ($attendance->{$timestampColumn} !== null) {
                    throw DomainError::invalidTransition('Absensi', 'sudah_'.$eventType, $eventType);
                }
                if ($eventType === 'check_out' && $attendance->check_in_at === null) {
                    throw DomainError::invalidTransition('Absensi', 'belum_check_in', 'check_out');
                }

                $attendance->update([$timestampColumn => $now]);
                AttendanceEvent::query()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'event_type' => $eventType,
                    'recorded_at' => $now,
                    'photo_path' => $path,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'accuracy_meters' => $data['accuracy_meters'] ?? null,
                    'device_info' => $data['device_info'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'created_at' => $now,
                ]);

                $this->audit->record($user, 'attendance.'.$eventType, 'attendance', $attendance->id, [
                    'event_type' => $eventType,
                    'recorded_at' => $now->toIso8601String(),
                ]);

                return $attendance->fresh(['events']);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    /** Validate client coordinates against the server-owned cafe location; browser GPS is not trusted as proof of identity. */
    private function assertWithinGeofence(array $data): void
    {
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        if (! is_numeric($latitude) || ! is_numeric($longitude)
            || (float) $latitude < -90 || (float) $latitude > 90
            || (float) $longitude < -180 || (float) $longitude > 180) {
            throw ValidationException::withMessages(['latitude' => 'Koordinat perangkat tidak valid.']);
        }

        $centerLatitude = config('attendance.geofence.latitude');
        $centerLongitude = config('attendance.geofence.longitude');
        if (! is_numeric($centerLatitude) || ! is_numeric($centerLongitude)
            || (float) $centerLatitude < -90 || (float) $centerLatitude > 90
            || (float) $centerLongitude < -180 || (float) $centerLongitude > 180) {
            throw new \App\Exceptions\DomainError(
                'ATTENDANCE_GEOFENCE_UNAVAILABLE',
                'Absensi sementara tidak tersedia. Silakan hubungi administrator.',
                503,
            );
        }

        $distance = $this->distanceInMeters(
            (float) $latitude,
            (float) $longitude,
            (float) $centerLatitude,
            (float) $centerLongitude,
        );
        $radius = (float) config('attendance.geofence.radius_meters', 30.0);
        if (! is_finite($radius) || $radius <= 0 || $radius !== 30.0) {
            throw new \App\Exceptions\DomainError(
                'ATTENDANCE_GEOFENCE_UNAVAILABLE',
                'Absensi sementara tidak tersedia. Silakan hubungi administrator.',
                503,
            );
        }

        // A one-micrometre tolerance avoids rejecting a mathematically exact boundary due to floating point rounding.
        if ($distance > $radius + 0.000001) {
            throw ValidationException::withMessages(['latitude' => 'Lokasi berada di luar radius absensi 30 meter.']);
        }
    }

    private function distanceInMeters(float $latitude, float $longitude, float $centerLatitude, float $centerLongitude): float
    {
        $earthRadiusMeters = 6371008.8;
        $lat1 = deg2rad($latitude);
        $lat2 = deg2rad($centerLatitude);
        $deltaLat = deg2rad($centerLatitude - $latitude);
        $deltaLon = deg2rad($centerLongitude - $longitude);
        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
        $a = min(1.0, max(0.0, $a));

        return 2 * $earthRadiusMeters * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function myHistory(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->query($filters)->where('user_id', $user->id)
            ->with(['user', 'events'])->paginate((int) ($filters['per_page'] ?? 30));
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        return $this->query($filters)->with(['user', 'events'])->paginate((int) ($filters['per_page'] ?? 30));
    }

    public function correct(Attendance $attendance, array $data, User $actor): Attendance
    {
        return DB::transaction(function () use ($attendance, $data, $actor) {
            $locked = Attendance::query()->lockForUpdate()->findOrFail($attendance->id);
            $before = [
                'check_in_at' => $locked->check_in_at?->toIso8601String(),
                'check_out_at' => $locked->check_out_at?->toIso8601String(),
                'status' => $locked->status,
                'notes' => $locked->notes,
            ];
            $changes = array_intersect_key($data, array_flip(['check_in_at', 'check_out_at', 'status', 'notes']));
            $locked->update($changes);
            $after = [
                'check_in_at' => $locked->fresh()->check_in_at?->toIso8601String(),
                'check_out_at' => $locked->fresh()->check_out_at?->toIso8601String(),
                'status' => $locked->status,
                'notes' => $locked->notes,
            ];
            $this->audit->record($actor, 'attendance.corrected', 'attendance', $locked->id, [
                'before' => $before,
                'after' => $after,
                'reason' => $data['reason'],
            ]);

            return $locked->fresh(['events']);
        });
    }

    private function query(array $filters)
    {
        return Attendance::query()
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('attendance_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('attendance_date', '<=', $to))
            ->orderByDesc('attendance_date')->orderByDesc('id');
    }

    private function forToday(User $user): ?Attendance
    {
        return Attendance::query()->where('user_id', $user->id)
            ->whereDate('attendance_date', now()->toDateString())->first();
    }
}
