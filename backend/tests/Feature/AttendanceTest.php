<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttendanceTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'attendance.geofence.latitude' => 37.5,
            'attendance.geofence.longitude' => -122.2,
            'attendance.geofence.radius_meters' => 30.0,
        ]);
    }

    public function test_staf_dapat_check_in_dan_check_out_sekali_per_hari(): void
    {
        Storage::fake('local');
        $user = $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/attendance/today-status')->assertOk()->assertJsonPath('data.checked_in', false);

        $this->post(self::API.'/attendance/check-out', $this->attendancePayload())->assertStatus(409);
        $this->post(self::API.'/attendance/check-in', $this->attendancePayload())->assertOk()->assertJsonPath('data.checked_in', true);
        $this->post(self::API.'/attendance/check-in', $this->attendancePayload())->assertStatus(409);
        $this->post(self::API.'/attendance/check-out', $this->attendancePayload())->assertOk()->assertJsonPath('data.checked_out', true);
        $this->post(self::API.'/attendance/check-out', $this->attendancePayload())->assertStatus(409);

        $this->assertSame(1, Attendance::query()->where('user_id', $user->id)->count());
        $this->assertSame(2, AttendanceEvent::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('attendance_events', ['user_id' => $user->id, 'event_type' => 'check_in', 'latitude' => 37.5, 'longitude' => -122.2]);
        $this->assertDatabaseHas('attendance_events', ['user_id' => $user->id, 'event_type' => 'check_in', 'accuracy_meters' => 7.25]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'attendance.check_in']);
        $event = AttendanceEvent::query()->where('user_id', $user->id)->where('event_type', 'check_in')->firstOrFail();
        Storage::disk('local')->assertExists($event->photo_path);
        $this->getJson(self::API.'/attendance/my-history')->assertOk()->assertJsonPath('data.0.user.id', $user->id);
        $this->get(self::API.'/attendance/events/'.$event->id.'/photo')->assertOk();
        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/attendance/events/'.$event->id.'/photo')->assertForbidden();
        $this->actingAsRole(UserRole::Manager);
        $this->get(self::API.'/attendance/events/'.$event->id.'/photo')->assertForbidden();
        $this->actingAsRole(UserRole::Owner);
        $this->get(self::API.'/attendance/events/'.$event->id.'/photo')->assertOk();
    }

    public function test_absensi_mewajibkan_foto_dan_koordinat_yang_valid(): void
    {
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/attendance/check-in', [])->assertUnprocessable();
        $this->post(self::API.'/attendance/check-in', ['photo' => UploadedFile::fake()->image('selfie.jpg'), 'latitude' => 91, 'longitude' => 0])->assertUnprocessable();
        $this->post(self::API.'/attendance/check-in', [...$this->attendancePayload(), 'accuracy_meters' => -1])->assertUnprocessable();
        $this->post(self::API.'/attendance/check-in', ['photo' => UploadedFile::fake()->createWithContent('selfie.jpg', 'not an image'), 'latitude' => 1, 'longitude' => 1])->assertUnprocessable();
    }

    public function test_geofence_menerima_lokasi_di_dalam_radius_dan_tepat_di_batas(): void
    {
        Storage::fake('local');
        $this->actingAsRole(UserRole::Kasir);
        config(['attendance.geofence.latitude' => 0.0, 'attendance.geofence.longitude' => 0.0]);

        $metersPerDegree = 111195.0802335329;
        $this->post(self::API.'/attendance/check-in', $this->attendancePayload(29 / $metersPerDegree, 0.0))->assertOk();

        $this->actingAsRole(UserRole::Manager);
        $boundaryLatitude = rad2deg(30 / 6371008.8);
        $this->post(self::API.'/attendance/check-in', $this->attendancePayload($boundaryLatitude, 0.0))->assertOk();
    }

    public function test_geofence_menolak_lokasi_di_luar_radius(): void
    {
        $this->actingAsRole(UserRole::Kasir);
        config(['attendance.geofence.latitude' => 0.0, 'attendance.geofence.longitude' => 0.0]);
        $outsideLatitude = rad2deg(30.01 / 6371008.8);

        $this->post(self::API.'/attendance/check-in', $this->attendancePayload($outsideLatitude, 0.0))
            ->assertUnprocessable()->assertJsonValidationErrors('latitude');
        $this->assertSame(0, Attendance::count());
    }

    public function test_geofence_gagal_tertutup_jika_lokasi_backend_belum_dikonfigurasi(): void
    {
        $this->actingAsRole(UserRole::Kasir);
        config(['attendance.geofence.latitude' => null, 'attendance.geofence.longitude' => null]);

        $this->post(self::API.'/attendance/check-in', $this->attendancePayload())
            ->assertStatus(503)->assertJsonPath('error.code', 'ATTENDANCE_GEOFENCE_UNAVAILABLE');
        config(['attendance.geofence.latitude' => 91.0, 'attendance.geofence.longitude' => 0.0]);
        $this->post(self::API.'/attendance/check-in', $this->attendancePayload())
            ->assertStatus(503)->assertJsonPath('error.code', 'ATTENDANCE_GEOFENCE_UNAVAILABLE');
        $this->assertSame(0, Attendance::count());
    }

    public function test_request_tidak_dapat_mengganti_koordinat_pusat_backend(): void
    {
        $this->actingAsRole(UserRole::Kasir);
        config(['attendance.geofence.latitude' => 12.345678, 'attendance.geofence.longitude' => 98.765432]);
        $payload = $this->attendancePayload(0.0, 0.0) + ['cafe_latitude' => 0.0, 'cafe_longitude' => 0.0];

        $response = $this->post(self::API.'/attendance/check-in', $payload)->assertUnprocessable();
        $this->assertSame(12.345678, config('attendance.geofence.latitude'));
        $this->assertSame(98.765432, config('attendance.geofence.longitude'));
        $this->assertStringNotContainsString('12.345678', $response->getContent());
        $this->assertStringNotContainsString('98.765432', $response->getContent());
    }

    public function test_rekap_hanya_dapat_diakses_pemegang_izin_view_all(): void
    {
        $staff = User::factory()->role(UserRole::Kasir)->create();
        Attendance::query()->create([
            'user_id' => $staff->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
            'check_in_at' => now(),
        ]);

        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/attendance/list')->assertForbidden();

        $this->actingAsRole(UserRole::Manager);
        $this->getJson(self::API.'/attendance/list')->assertOk()->assertJsonPath('data.0.user.id', $staff->id);
    }

    public function test_owner_hanya_memantau_absensi_dan_staff_yang_diizinkan_bisa_mencatat(): void
    {
        Storage::fake('local');
        $owner = $this->actingAsRole(UserRole::Owner);
        $this->getJson(self::API.'/attendance/list')->assertOk();
        $this->getJson(self::API.'/attendance/today-status')->assertForbidden();
        $this->post(self::API.'/attendance/check-in', $this->attendancePayload())->assertForbidden();
        $this->assertSame(0, Attendance::query()->where('user_id', $owner->id)->count());

        foreach ([UserRole::Manager, UserRole::Kasir, UserRole::BaristaKitchen] as $role) {
            $staff = $this->actingAsRole($role);
            $this->post(self::API.'/attendance/check-in', $this->attendancePayload())->assertOk();
            $this->assertDatabaseHas('attendance_events', ['user_id' => $staff->id, 'event_type' => 'check_in']);
        }
    }

    private function attendancePayload(float $latitude = 37.5, float $longitude = -122.2): array
    {
        return [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy_meters' => 7.25,
            'device_info' => 'Feature test browser',
        ];
    }
}
