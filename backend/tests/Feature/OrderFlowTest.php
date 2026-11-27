<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\Table;

/** Alur inti: pesan → verifikasi kasir → tiket stasiun → produksi → selesai. */
class OrderFlowTest extends ApiTestCase
{
    public function test_pesanan_campuran_menghasilkan_dua_tiket_tanpa_mengubah_stok_legacy(): void
    {
        $this->table('1');
        $latte = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18], 'Susu' => [2000, 150]], false, 20000);
        $nasi = $this->menuWithRecipe('Nasi Goreng', ['Nasi' => [5000, 200]], true, 30000);

        // Harga dari klien diabaikan; total dihitung server.
        $resp = $this->placeOrder([['menu_id' => $latte->id, 'qty' => 2, 'price' => 1], ['menu_id' => $nasi->id, 'qty' => 1]], 'qris')
            ->assertCreated()->assertJsonPath('data.status.code', 'menunggu_pembayaran')->assertJsonPath('data.total', 70000);
        $token = $resp->json('data.public_token');
        $this->assertNull($resp->json('data.id'), 'pelanggan tidak menerima ID internal');

        $order = Order::query()->where('public_token', $token)->firstOrFail();
        $this->assertSame(0, $order->tickets()->count(), 'tiket baru dibuat setelah pembayaran terverifikasi');

        $this->actingAsRole(UserRole::Kasir);
        $paymentId = $order->payment->id;
        $this->postJson(self::API."/cashier/payments/$paymentId/verify")->assertOk()->assertJsonPath('data.status.code', 'dibayar')
            ->assertJsonPath('data.payment.status.code', 'verified');
        $this->assertSame(['barista', 'kitchen'], $order->tickets()->orderBy('station')->pluck('station')->map->value->all());
        $this->assertNotNull($order->payment()->first()->transaction_number);
        $this->assertSame('1000.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $this->assertSame('2000.000', Ingredient::where('name', 'Susu')->value('stock'));
        $this->assertSame('5000.000', Ingredient::where('name', 'Nasi')->value('stock'));
        $this->assertSame(0, StockMovement::where('type', 'pemakaian_pesanan')->count());

        // Inventori legacy tidak memengaruhi lifecycle tiket.
        $barista = $this->actingAsRole(UserRole::BaristaKitchen);
        $ticket = $order->tickets()->where('station', 'barista')->firstOrFail();
        $kitchenTicket = $order->tickets()->where('station', 'kitchen')->firstOrFail();
        $this->postJson(self::API."/station/kitchen/tickets/{$kitchenTicket->id}/start")->assertNotFound();
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/start")->assertOk()->assertJsonPath('data.status.code', 'diproses');
        $this->assertSame('1000.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $this->assertSame('2000.000', Ingredient::where('name', 'Susu')->value('stock'));
        $this->assertSame('5000.000', Ingredient::where('name', 'Nasi')->value('stock'));
        $this->assertSame(0, StockMovement::where('type', 'pemakaian_pesanan')->count());
        $this->assertSame($barista->id, $ticket->refresh()->handled_by);

        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/complete")->assertOk();
        $this->assertSame('diproses', $order->refresh()->status->value, 'pesanan belum selesai: tiket Kitchen masih antre');

        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'kitchen']);
        $kt = $order->tickets()->where('station', 'kitchen')->firstOrFail();
        $this->postJson(self::API."/station/kitchen/tickets/{$kt->id}/start")->assertOk();
        $this->postJson(self::API."/station/kitchen/tickets/{$kt->id}/complete")->assertOk();
        $this->assertSame('diproses', $order->refresh()->status->value);
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/orders/{$order->id}/complete")->assertOk();
        $this->assertSame('selesai', $order->refresh()->status->value);

        $this->getJson(self::API."/customer/orders/$token")->assertOk()->assertJsonPath('data.status.code', 'selesai');
    }

    public function test_stok_legacy_tidak_menghalangi_verifikasi_pembayaran(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [100, 18]]);
        $response = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $order = Order::where('public_token', $response->json('data.public_token'))->firstOrFail();
        Ingredient::where('name', 'Kopi')->update(['stock' => 5]); // habis sebelum diproses
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->assertSame('verified', $order->payment->refresh()->status->value);
        $this->assertSame('dibayar', $order->refresh()->status->value);
        $this->assertSame(1, $order->tickets()->count());
        $this->assertSame('5.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $ticket = $order->tickets()->firstOrFail();
        $this->actingAsRole(UserRole::BaristaKitchen);
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/start")->assertOk();
        $this->assertSame('5.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $this->assertSame(0, StockMovement::where('type', 'pemakaian_pesanan')->count());
    }

    public function test_ketersediaan_menu_tidak_ditentukan_oleh_stok_legacy(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [0, 18]]);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
    }

    public function test_verifikasi_ganda_ditolak_dan_tunai_kurang_ditolak(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]], false, 20000);
        $response = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $paymentId = Order::where('public_token', $response->json('data.public_token'))->firstOrFail()->payment->id;
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/payments/$paymentId/verify", ['amount_received' => 5000])->assertStatus(422)->assertJsonPath('error.code', 'AMOUNT_TOO_LOW');
        $this->postJson(self::API."/cashier/payments/$paymentId/verify", ['amount_received' => 50000])->assertOk()->assertJsonPath('data.payment.change', 30000);
        $this->postJson(self::API."/cashier/payments/$paymentId/verify")->assertStatus(409)->assertJsonPath('error.code', 'PAYMENT_ALREADY_PROCESSED');
        $this->assertSame(0, StockMovement::where('type', 'pemakaian_pesanan')->count());
    }

    public function test_pembatalan_pelanggan_ditolak_setelah_item_mulai_diproses(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $token = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->json('data.public_token');
        $detailId = Order::where('public_token', $token)->firstOrFail()->details()->firstOrFail()->id;
        $this->postJson(self::API."/customer/orders/$token/cancel", ['detail_id' => $detailId, 'qty' => 1, 'reason' => 'Berubah pikiran'])->assertOk()->assertJsonPath('data.status.code', 'dibatalkan');

        $token2 = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->json('data.public_token');
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.Order::where('public_token', $token2)->firstOrFail()->payment->id.'/verify')->assertOk();
        $order2 = Order::where('public_token', $token2)->firstOrFail();
        $ticket = $order2->tickets()->firstOrFail();
        $detail2 = $order2->details()->firstOrFail();
        $this->actingAsRole(UserRole::BaristaKitchen);
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/items/{$detail2->id}/start")->assertOk();
        $this->postJson(self::API."/customer/orders/$token2/cancel", ['detail_id' => $detail2->id, 'qty' => 1, 'reason' => 'Berubah pikiran'])->assertStatus(409)->assertJsonPath('error.code', 'ORDER_NOT_CANCELLABLE');
    }

    public function test_client_request_id_mencegah_pesanan_ganda(): void
    {
        $table = $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $opened = $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token, 'customer_name' => 'Pelanggan'])->assertOk();
        $this->withHeader('X-Table-Session', $opened->json('data.session_token'));
        $body = ['table_number' => '1', 'payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]], 'client_request_id' => '7b0e6e5a-2d0f-4b86-9f64-0d1f5a6c9a11'];
        $a = $this->postJson(self::API.'/customer/orders', $body)->assertCreated()->json('data.public_token');
        $b = $this->postJson(self::API.'/customer/orders', $body)->assertCreated()->json('data.public_token');
        $this->assertSame($a, $b);
        $this->assertSame(1, Order::query()->where('public_token', $a)->count());
    }

    public function test_nomor_meja_tidak_sesuai_token_qr_ditolak(): void
    {
        $t1 = $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $opened = $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $t1->qr_token, 'customer_name' => 'Pelanggan'])->assertOk();
        $this->withHeader('X-Table-Session', $opened->json('data.session_token'));
        $body = ['payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]]];
        $this->postJson(self::API.'/customer/orders', [...$body, 'table_number' => '2'])->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
        $this->postJson(self::API.'/customer/orders', [...$body, 'table_number' => '1'])->assertCreated();
    }

    public function test_pembatalan_owner_membuat_request_refund_tanpa_mengubah_payment(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $response = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 2]])->assertCreated();
        $order = Order::where('public_token', $response->json('data.public_token'))->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->actingAsRole(UserRole::Owner);
        $detail = $order->details()->firstOrFail();
        $this->postJson(self::API."/orders/{$order->id}/items/cancel", ['detail_id' => $detail->id, 'qty' => 2, 'reason' => 'Salah pesan'])->assertOk()->assertJsonPath('data.status.code', 'dibatalkan');
        $this->assertSame('verified', $order->payments()->first()->status->value);
        $this->assertDatabaseHas('refund_requests', ['payment_id' => $order->payment->id, 'amount' => 40000, 'source' => 'owner_cancellation', 'status' => 'requested']);
        $this->assertSame('1000.000', Ingredient::where('name', 'Kopi')->value('stock'));
    }

    public function test_pembayaran_tidak_dibatasi_stok_legacy(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Kopi Terbatas', ['Kopi' => [10, 10]]);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $first = Order::query()->latest('id')->firstOrFail();
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $second = Order::query()->latest('id')->firstOrFail();

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$first->payment->id.'/verify')->assertOk();
        $this->postJson(self::API.'/cashier/payments/'.$second->payment->id.'/verify')->assertOk();

        $this->assertSame('10.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $this->assertSame(0, StockMovement::where('type', 'pemakaian_pesanan')->count());
        $this->assertSame(1, $second->tickets()->count());
    }
}
