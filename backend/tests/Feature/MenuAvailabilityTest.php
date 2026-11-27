<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\Order;

class MenuAvailabilityTest extends ApiTestCase
{
    public function test_station_role_can_only_change_availability_for_its_station(): void
    {
        $drink = Menu::factory()->create(['name' => 'Minuman Test']);
        $food = Menu::factory()->kitchen()->create(['name' => 'Makanan Test']);

        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $this->getJson(self::API.'/admin/menus')->assertOk()->assertJsonFragment(['name' => 'Minuman Test'])->assertJsonMissing(['name' => 'Makanan Test']);
        $this->patchJson(self::API."/admin/menus/{$drink->id}/availability", ['is_available' => false])->assertOk();
        $this->patchJson(self::API."/admin/menus/{$food->id}/availability", ['is_available' => false])->assertForbidden();

        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'kitchen']);
        $this->patchJson(self::API."/admin/menus/{$food->id}/availability", ['is_available' => false])->assertOk();
        $this->patchJson(self::API."/admin/menus/{$drink->id}/availability", ['is_available' => true])->assertForbidden();
    }

    public function test_only_owner_and_production_roles_can_toggle_availability(): void
    {
        $menu = Menu::factory()->create();
        $this->actingAsRole(UserRole::Owner);
        $this->patchJson(self::API."/admin/menus/{$menu->id}/availability", ['is_available' => false])->assertOk();

        $this->actingAsRole(UserRole::Manager);
        $this->patchJson(self::API."/admin/menus/{$menu->id}/availability", ['is_available' => true])->assertForbidden();
        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/admin/menus')->assertOk()->assertJsonFragment(['is_available' => false]);
        $this->patchJson(self::API."/admin/menus/{$menu->id}/availability", ['is_available' => true])->assertForbidden();
    }

    public function test_customer_sees_habis_and_unavailable_checkout_creates_no_order_or_payment(): void
    {
        $table = $this->table('1');
        $ordersBefore = Order::count();
        $paymentsBefore = \App\Models\Payment::count();
        $session = $this->postJson(self::API.'/customer/sessions/open', [
            'qr_token' => $table->qr_token, 'customer_name' => 'Pelanggan Test Ketersediaan',
        ])->assertOk()->json('data.session_token');
        $menu = Menu::factory()->create(['name' => 'Menu Habis', 'is_available' => false]);
        $this->getJson(self::API.'/customer/menus')->assertOk()
            ->assertJsonFragment(['name' => 'Menu Habis', 'is_available' => false, 'is_orderable' => false]);
        $this->postJson(self::API.'/customer/cart/validate', ['items' => [['menu_id' => $menu->id, 'qty' => 1]]], ['X-Table-Session' => $session])
            ->assertOk()->assertJsonPath('data.issues.0.code', 'MENU_UNAVAILABLE');
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertStatus(409)->assertJsonPath('error.code', 'MENU_UNAVAILABLE');
        $this->assertSame($ordersBefore, Order::count());
        $this->assertSame($paymentsBefore, \App\Models\Payment::count());
    }

    public function test_owner_can_create_and_update_menu_while_manager_cannot_change_availability(): void
    {
        $category = $this->category('Kategori Owner Test', 'minuman', 'barista');
        $this->actingAsRole(UserRole::Owner);
        $created = $this->postJson(self::API.'/admin/menus', [
            'name' => 'Menu Owner Baru', 'price' => 18000, 'category_id' => $category->id,
        ])->assertCreated()->assertJsonPath('data.name', 'Menu Owner Baru');

        $menuId = $created->json('data.id');
        $this->putJson(self::API."/admin/menus/{$menuId}", [
            'name' => 'Menu Owner Diperbarui', 'price' => 19000, 'category_id' => $category->id,
        ])->assertOk()->assertJsonPath('data.name', 'Menu Owner Diperbarui');

        $this->actingAsRole(UserRole::Manager);
        $this->patchJson(self::API."/admin/menus/{$menuId}/availability", ['is_available' => false])->assertForbidden();
    }

    public function test_toggling_menu_does_not_change_existing_order_snapshot(): void
    {
        $this->table('1');
        $menu = Menu::factory()->create(['name' => 'Menu Riwayat', 'price' => 12000]);
        $response = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $order = Order::query()->where('public_token', $response->json('data.public_token'))->firstOrFail();
        $detail = $order->details()->firstOrFail();

        $this->actingAsRole(UserRole::Owner);
        $this->patchJson(self::API."/admin/menus/{$menu->id}/availability", ['is_available' => false])->assertOk();

        $this->assertSame('Menu Riwayat', $detail->refresh()->menu_name);
        $this->assertSame(12000, $detail->unit_price);
        $this->assertSame(1, Order::query()->whereKey($order->id)->count());
        $this->assertSame(1, $order->payments()->count());
    }
}
