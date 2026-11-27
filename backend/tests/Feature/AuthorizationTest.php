<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class AuthorizationTest extends ApiTestCase
{
    public function test_role_produksi_hanya_membuka_antrean_station_akun(): void
    {
        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $this->getJson(self::API.'/station/barista/tickets')->assertOk();
        $this->getJson(self::API.'/station/kitchen/tickets')->assertStatus(404);

        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'kitchen']);
        $this->getJson(self::API.'/station/kitchen/tickets')->assertOk();
        $this->getJson(self::API.'/station/barista/tickets')->assertStatus(404);
    }

    public function test_kasir_tanpa_izin_tidak_bisa_membuka_antrean_stasiun_404(): void
    {
        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/station/kitchen/tickets')->assertStatus(404)->assertJsonPath('error.code', 'NOT_FOUND');
        $this->getJson(self::API.'/station/barista/tickets')->assertStatus(404)->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_izin_khusus_membuka_akses_stasiun(): void
    {
        $owner = $this->actingAsRole(UserRole::Owner);
        $kasir = User::factory()->role(UserRole::Kasir)->create();
        $this->putJson(self::API."/admin/users/{$kasir->id}/permissions", ['permissions' => ['queue.kitchen']])->assertOk();

        $this->actingAsRole(UserRole::Kasir, []); // pengguna kasir lain tanpa izin khusus tetap ditolak
        $this->getJson(self::API.'/station/kitchen/tickets')->assertStatus(404);

        \Laravel\Sanctum\Sanctum::actingAs($kasir->refresh(), ['*']);
        $this->getJson(self::API.'/station/kitchen/tickets')->assertOk();
        $this->getJson(self::API.'/station/barista/tickets')->assertStatus(404);
        $this->assertNotNull($owner);
    }

    public function test_izin_berbahaya_tidak_dapat_diberikan(): void
    {
        $this->actingAsRole(UserRole::Owner);
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
        // Purchasing and inventory routes are retired, so these must remain absent (404).
        $this->postJson(self::API.'/purchasing/purchases', [])->assertNotFound();
        $this->postJson(self::API.'/inventory/stock-adjustments', [])->assertNotFound();
        $this->postJson(self::API.'/station/barista/tickets/1/start')->assertStatus(403);
    }

    public function test_manager_tidak_boleh_mengubah_akun_owner(): void
    {
        $this->actingAsRole(UserRole::Manager);
        $owner = User::factory()->role(UserRole::Owner)->create();
        $this->patchJson(self::API."/admin/users/{$owner->id}/status", ['is_active' => false])->assertStatus(403);
        $this->postJson(self::API.'/admin/users', ['name' => 'Bos Baru', 'username' => 'bos', 'email' => 'bos@x.test', 'role' => 'owner', 'password' => 'abcdefgh1'])->assertForbidden();
    }

    public function test_pelanggan_tidak_bisa_mengakses_pesanan_lain_dengan_id_berurut(): void
    {
        $ordersBefore = Order::count();
        $this->getJson(self::API.'/customer/orders/1')->assertUnauthorized();
        $this->assertSame($ordersBefore, Order::count());
    }
}
