<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;

class AuthTest extends ApiTestCase
{
    public function test_login_dengan_email_atau_username(): void
    {
        User::factory()->create(['username' => 'ucup', 'email' => 'ucup@cafe.test', 'password' => 'rahasia-123']);

        foreach (['ucup', 'UCUP@cafe.test'] as $login) {
            $this->postJson(self::API.'/auth/login', ['login' => $login, 'password' => 'rahasia-123'])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonStructure(['data' => ['token', 'expires_at', 'user' => ['id', 'name', 'role' => ['code', 'label'], 'permissions']], 'meta' => ['request_id', 'server_time']]);
        }
    }

    public function test_kata_sandi_salah_menghasilkan_422_seragam(): void
    {
        User::factory()->create(['username' => 'ucup']);
        $this->postJson(self::API.'/auth/login', ['login' => 'ucup', 'password' => 'salah-salah'])
            ->assertStatus(422)->assertJsonPath('success', false)->assertJsonPath('error.code', 'VALIDATION_FAILED')
            ->assertJsonStructure(['errors' => ['login']]);
    }

    public function test_akun_nonaktif_ditolak(): void
    {
        User::factory()->inactive()->create(['username' => 'nonaktif', 'password' => 'rahasia-123']);
        $this->postJson(self::API.'/auth/login', ['login' => 'nonaktif', 'password' => 'rahasia-123'])->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_tanpa_token_401_json_bukan_redirect(): void
    {
        $this->getJson(self::API.'/auth/me')->assertStatus(401)->assertJsonPath('error.code', 'UNAUTHENTICATED');
        $this->get(self::API.'/auth/me', ['Accept' => 'text/html'])->assertStatus(401)->assertHeader('content-type', 'application/json');
    }

    public function test_rute_tak_dikenal_404_json_dan_metode_salah_405_json(): void
    {
        $this->getJson(self::API.'/tidak-ada')->assertStatus(404)->assertJsonPath('error.code', 'NOT_FOUND');
        $this->putJson(self::API.'/health')->assertStatus(405)->assertJsonPath('error.code', 'METHOD_NOT_ALLOWED');
    }

    public function test_me_memuat_izin_efektif_dan_x_request_id(): void
    {
        $this->actingAsRole(UserRole::Barista);
        $this->getJson(self::API.'/auth/me', ['X-Request-Id' => 'req-12345678'])
            ->assertOk()->assertHeader('X-Request-Id', 'req-12345678')
            ->assertJsonPath('data.role.code', 'barista')
            ->assertJsonFragment(['queue.barista']);
    }

    public function test_menonaktifkan_pengguna_mencabut_tokennya(): void
    {
        $admin = $this->actingAsRole(UserRole::Admin);
        $staff = User::factory()->role(UserRole::Kasir)->create();
        $staff->createToken('api');
        $this->patchJson(self::API."/admin/users/{$staff->id}/status", ['is_active' => false])->assertOk();
        $this->assertSame(0, $staff->tokens()->count());
        $this->assertNotNull($admin);
    }
}
