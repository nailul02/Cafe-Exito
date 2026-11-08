<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class AuthorizationTest extends ApiTestCase
{
    public function test_barista_tidak_bisa_membuka_antrean_kitchen_404(): void
    {
        $this->actingAsRole(UserRole::Barista);
        $this->getJson(self::API.'/station/kitchen/tickets')->assertStatus(404)->assertJsonPath('error.code', 'NOT_FOUND');
        $this->getJson(self::API.'/station/barista/tickets')->assertOk();
    }

    public function test_izin_khusus_membuka_akses_stasiun_lain(): void
    {
        $admin = $this->actingAsRole(UserRole::Admin);
        $barista = User::factory()->role(UserRole::Barista)->create();
        $this->putJson(self::API."/admin/users/{$barista->id}/permissions", ['permissions' => ['queue.kitchen']])->assertOk();

        $this->actingAsRole(UserRole::Barista, []); // pengguna baru tanpa izin khusus tetap ditolak
        $this->getJson(self::API.'/station/kitchen/tickets')->assertStatus(404);

        \Laravel\Sanctum\Sanctum::actingAs($barista->refresh(), ['*']);
        $this->getJson(self::API.'/station/kitchen/tickets')->assertOk();
        $this->assertNotNull($admin);
    }

    public function test_izin_berbahaya_tidak_dapat_diberikan(): void
    {
        $this->actingAsRole(UserRole::Admin);
        $kasir = User::factory()->role(UserRole::Kasir)->create();
        $this->putJson(self::API."/admin/users/{$kasir->id}/permissions", ['permissions' => ['users.manage']])->assertStatus(422);
    }

    public function test_kasir_tidak_bisa_mengelola_menu_atau_pengguna(): void
    {
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/admin/menus', ['name' => 'X', 'price' => 1000, 'category_id' => 1])->assertStatus(403);
        $this->getJson(self::API.'/admin/users')->assertStatus(403);
        $this->getJson(self::API.'/reports/sales')->assertStatus(403);
    }

    public function test_owner_hanya_membaca(): void
    {
        $this->actingAsRole(UserRole::Owner);
        $this->getJson(self::API.'/dashboard/owner')->assertOk();
        $this->getJson(self::API.'/reports/sales?period=weekly')->assertOk();
        $this->postJson(self::API.'/purchasing/purchases', [])->assertStatus(403);
        $this->postJson(self::API.'/inventory/stock-adjustments', [])->assertStatus(403);
        $this->postJson(self::API.'/station/barista/tickets/1/start')->assertStatus(403);
    }

    public function test_admin_tidak_boleh_mengubah_akun_owner(): void
    {
        $this->actingAsRole(UserRole::Admin);
        $owner = User::factory()->role(UserRole::Owner)->create();
        $this->patchJson(self::API."/admin/users/{$owner->id}/status", ['is_active' => false])->assertStatus(403);
        $this->postJson(self::API.'/admin/users', ['name' => 'Bos Baru', 'username' => 'bos', 'email' => 'bos@x.test', 'role' => 'owner', 'password' => 'abcdefgh1'])->assertStatus(422);
    }

    public function test_pelanggan_tidak_bisa_mengakses_pesanan_lain_dengan_id_berurut(): void
    {
        $this->getJson(self::API.'/customer/orders/1')->assertStatus(404);
        $this->assertSame(0, Order::count());
    }
}
