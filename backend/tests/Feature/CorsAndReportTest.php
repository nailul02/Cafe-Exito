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
        $paidResponse = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 2]], 'qris')->assertCreated();
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'tunai')->assertCreated(); // tetap menunggu bayar
        $paid = Order::query()->where('public_token', $paidResponse->json('data.public_token'))->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$paid->payment->id.'/verify')->assertOk();

        $this->actingAsRole(UserRole::Manager);
        $this->getJson(self::API.'/reports/sales?period=daily')->assertOk()
            ->assertJsonPath('data.totals.orders', 1)->assertJsonPath('data.totals.revenue', 40000)
            ->assertJsonPath('data.by_method.0.method', 'qris')->assertJsonPath('data.by_method.0.amount', 40000);
    }

    public function test_rekap_mengecualikan_pesanan_batal_dan_mengurangi_pembatalan_parsial(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte laporan', ['Kopi' => [10000, 18]], false, 20000);

        $active = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 3]], 'qris')->assertCreated();
        $cancelled = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'qris')->assertCreated();
        $activeOrder = Order::where('public_token', $active->json('data.public_token'))->firstOrFail();
        $cancelledOrder = Order::where('public_token', $cancelled->json('data.public_token'))->firstOrFail();

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$activeOrder->payment->id.'/verify')->assertOk();
        $this->postJson(self::API.'/cashier/payments/'.$cancelledOrder->payment->id.'/verify')->assertOk();

        $this->actingAsRole(UserRole::Owner);
        $activeDetail = $activeOrder->details()->firstOrFail();
        $this->postJson(self::API."/orders/{$activeOrder->id}/items/cancel", [
            'detail_id' => $activeDetail->id,
            'qty' => 1,
            'reason' => 'Pembatalan sebagian untuk uji laporan',
        ])->assertOk()->assertJsonPath('data.status.code', 'dibayar');

        $cancelledDetail = $cancelledOrder->details()->firstOrFail();
        $this->postJson(self::API."/orders/{$cancelledOrder->id}/items/cancel", [
            'detail_id' => $cancelledDetail->id,
            'qty' => 1,
            'reason' => 'Pesanan dibatalkan untuk uji laporan',
        ])->assertOk()->assertJsonPath('data.status.code', 'dibatalkan');

        $this->actingAsRole(UserRole::Manager);
        $report = $this->getJson(self::API.'/reports/sales?period=daily')->assertOk();
        $report->assertJsonPath('data.totals.orders', 1)
            ->assertJsonPath('data.totals.revenue', 40000)
            ->assertJsonPath('data.totals.items', 2)
            ->assertJsonPath('data.by_method.0.orders', 1)
            ->assertJsonPath('data.by_method.0.amount', 40000);

        $this->assertSame(40000, array_sum(array_column($report->json('data.series'), 'amount')));
        $this->assertSame(1, array_sum(array_column($report->json('data.series'), 'orders')));
        $this->assertSame(2, $report->json('data.top_menus.0.qty'));
    }

    public function test_health_publik_tanpa_detail_internal(): void
    {
        $this->getJson(self::API.'/health')->assertOk()->assertExactJsonStructure(['success', 'message', 'data' => ['status'], 'meta' => ['request_id', 'server_time']]);
    }
}
