<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;

class CorsAndReportTest extends ApiTestCase
{
    public function test_preflight_origin_yang_diizinkan_lolos_dan_header_kustom_diizinkan(): void
    {
        $this->call('OPTIONS', self::API.'/customer/orders', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:5173', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,x-table-token,authorization',
        ])->assertStatus(204)->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    public function test_origin_tidak_dikenal_tidak_mendapat_header_cors(): void
    {
        $resp = $this->call('OPTIONS', self::API.'/customer/orders', [], [], [], [
            'HTTP_ORIGIN' => 'http://evil.example', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);
        $this->assertNull($resp->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_laporan_hanya_menghitung_pembayaran_terverifikasi(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [10000, 18]], false, 20000);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 2]], 'qris')->assertCreated();
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'tunai')->assertCreated(); // tetap menunggu bayar
        $paid = Order::query()->orderBy('id')->firstOrFail();

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$paid->payment->id.'/verify')->assertOk();

        $this->actingAsRole(UserRole::Admin);
        $this->getJson(self::API.'/reports/sales?period=daily')->assertOk()
            ->assertJsonPath('data.totals.orders', 1)->assertJsonPath('data.totals.revenue', 40000)
            ->assertJsonPath('data.by_method.0.method', 'qris')->assertJsonPath('data.by_method.0.amount', 40000);
    }

    public function test_health_publik_tanpa_detail_internal(): void
    {
        $this->getJson(self::API.'/health')->assertOk()->assertExactJsonStructure(['success', 'message', 'data' => ['status'], 'meta' => ['request_id', 'server_time']]);
    }
}
