<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\PermissionService;

/** @mixin \App\Models\Attendance */
class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = $this->user?->role;
        $permissions = app(PermissionService::class);
        $mayViewFullReport = $permissions->has($request->user(), 'attendance.report');
        $mayViewEvidence = $permissions->has($request->user(), 'attendance.view_all');
        $mayViewOwnEvents = $request->user()?->id === $this->user_id;

        return [
            'id' => $this->id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'status' => $this->statusPayload($mayViewFullReport),
            'check_in_at' => $this->check_in_at?->toIso8601String(),
            'check_out_at' => $this->check_out_at?->toIso8601String(),
            'checked_in' => $this->check_in_at !== null,
            'checked_out' => $this->check_out_at !== null,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'role' => ['code' => $role?->value, 'label' => $role?->label()],
                'station' => $this->user->station?->value,
            ]),
            'events' => $this->whenLoaded('events', fn () => ($mayViewEvidence || $mayViewOwnEvents) ? $this->events->map(fn ($event) => [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'recorded_at' => $event->recorded_at?->toIso8601String(),
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'accuracy_meters' => $event->accuracy_meters,
                'device_info' => $event->device_info,
                'photo_url' => url('/api/v1/attendance/events/'.$event->id.'/photo'),
            ])->values() : []),
        ];
    }

    private function statusPayload(bool $mayViewFullReport): array
    {
        $status = $this->status;
        if ($mayViewFullReport && $status === 'present' && $this->check_in_at !== null) {
            $expectedStart = $this->attendance_date->copy()->setTime(16, 0);
            if ($this->check_in_at->greaterThan($expectedStart)) {
                $status = 'late';
            }
        }

        return ['code' => $status, 'label' => match ($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            default => ucfirst($status),
        }];
    }
}
