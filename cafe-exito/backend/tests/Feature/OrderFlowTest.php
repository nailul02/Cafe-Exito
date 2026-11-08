<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\StockMovement;

/** Alur inti: pesan → verifikasi kasir → tiket stasiun → mulai (stok berkurang) → selesai. */
class OrderFlowTest extends ApiTestCase
{
    public function test_pesanan_campuran_menghasilkan_dua_tiket_dan_stok_berkurang_saat_mulai(): void
    {
        $this->table('1');
        $latte = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18], 'Susu' => [2000, 150]], false, 20000);
        $nasi = $this->menuWithRecipe('Nasi Goreng', ['Nasi' => [5000, 200]], true, 30000);

        // Harga dari klien diabaikan; total dihitung server.
        $resp = $this->placeOrder([['menu_id' => $latte->id, 'qty' => 2, 'price' => 1], ['menu_id' => $nasi->id, 'qty' => 1]], 'qris')
            ->assertCreated()->assertJsonPath('data.status.code', 'menunggu_pembayaran')->assertJsonPath('data.total', 70000);
        $token = $resp->json('data.public_token');
        $this->assertNull($resp->json('data.id'), 'pelanggan tidak menerima ID internal');

        $order = Order::query()->firstOrFail();
        $this->assertSame(0, $order->tickets()->count(), 'tiket baru dibuat setelah pembayaran terverifikasi');

        $this->actingAsRole(UserRole::Kasir);
        $paymentId = $order->payment->id;
        $this->postJson(self::API."/cashier/payments/$paymentId/verify")->assertOk()->assertJsonPath('data.status.code', 'dibayar')
            ->assertJsonPath('data.payment.status.code', 'verified');
        $this->assertSame(['barista', 'kitchen'], $order->tickets()->orderBy('station')->pluck('station')->map->value->all());
        $this->assertNotNull($order->payment()->first()->transaction_number);

        // Barista memulai tiketnya: stok kopi & susu berkurang, stok nasi (Kitchen) belum.
        $barista = $this->actingAsRole(UserRole::Barista);
        $ticket = $order->tickets()->where('station', 'barista')->firstOrFail();
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/start")->assertOk()->assertJsonPath('data.status.code', 'diproses');
        $this->assertSame('964.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $this->assertSame('1700.000', Ingredient::where('name', 'Susu')->value('stock'));
        $this->assertSame('5000.000', Ingredient::where('name', 'Nasi')->value('stock'));
        $this->assertSame(2, StockMovement::where('type', 'pemakaian_pesanan')->count());
        $this->assertSame($barista->id, $ticket->refresh()->handled_by);

        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/complete")->assertOk();
        $this->assertSame('diproses', $order->refresh()->status->value, 'pesanan belum selesai: tiket Kitchen masih antre');

        $this->actingAsRole(UserRole::Kitchen);
        $kt = $order->tickets()->where('station', 'kitchen')->firstOrFail();
        $this->postJson(self::API."/station/kitchen/tickets/{$kt->id}/start")->assertOk();
        $this->postJson(self::API."/station/kitchen/tickets/{$kt->id}/complete")->assertOk();
        $this->assertSame('selesai', $order->refresh()->status->value);

        $this->getJson(self::API."/customer/orders/$token")->assertOk()->assertJsonPath('data.status.code', 'selesai');
    }

    public function test_stok_tidak_cukup_menolak_mulai_dan_tiket_tetap_antre(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [100, 18]]);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $order = Order::firstOrFail();
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();

        Ingredient::where('name', 'Kopi')->update(['stock' => 5]); // habis sebelum diproses
        $this->actingAsRole(UserRole::Barista);
        $ticket = $order->tickets()->firstOrFail();
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/start")->assertStatus(409)->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');
        $this->assertSame('antre', $ticket->refresh()->status->value);
        $this->assertSame('5.000', Ingredient::where('name', 'Kopi')->value('stock'));
    }

    public function test_menu_dengan_bahan_habis_tidak_bisa_dipesan(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [0, 18]]);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertStatus(409)->assertJsonPath('error.code', 'MENU_UNAVAILABLE');
    }

    public function test_verifikasi_ganda_ditolak_dan_tunai_kurang_ditolak(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]], false, 20000);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->assertCreated();
        $paymentId = Order::firstOrFail()->payment->id;
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/payments/$paymentId/verify", ['amount_received' => 5000])->assertStatus(422)->assertJsonPath('error.code', 'AMOUNT_TOO_LOW');
        $this->postJson(self::API."/cashier/payments/$paymentId/verify", ['amount_received' => 50000])->assertOk()->assertJsonPath('data.payment.change', 30000);
        $this->postJson(self::API."/cashier/payments/$paymentId/verify")->assertStatus(409)->assertJsonPath('error.code', 'PAYMENT_ALREADY_PROCESSED');
    }

    public function test_pembatalan_pelanggan_hanya_saat_menunggu_pembayaran(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $token = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->json('data.public_token');
        $this->postJson(self::API."/customer/orders/$token/cancel")->assertOk()->assertJsonPath('data.status.code', 'dibatalkan');

        $token2 = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]])->json('data.public_token');
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.Order::where('public_token', $token2)->firstOrFail()->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token2/cancel")->assertStatus(409)->assertJsonPath('error.code', 'ORDER_NOT_CANCELLABLE');
    }

    public function test_client_request_id_mencegah_pesanan_ganda(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $body = ['table_number' => '1', 'payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]], 'client_request_id' => '7b0e6e5a-2d0f-4b86-9f64-0d1f5a6c9a11'];
        $a = $this->postJson(self::API.'/customer/orders', $body)->assertCreated()->json('data.public_token');
        $b = $this->postJson(self::API.'/customer/orders', $body)->assertCreated()->json('data.public_token');
        $this->assertSame($a, $b);
        $this->assertSame(1, Order::count());
    }

    public function test_nomor_meja_tidak_sesuai_token_qr_ditolak(): void
    {
        $t1 = $this->table('1');
        $this->table('2');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'tunai', '2', ['X-Table-Token' => $t1->qr_token])->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'tunai', '1', ['X-Table-Token' => $t1->qr_token])->assertCreated();
    }

    public function test_pembatalan_admin_pembayaran_menjadi_refund_dan_stok_dikembalikan_bila_diminta(): void
    {
        $this->table('1');
        $menu = $this->menuWithRecipe('Latte', ['Kopi' => [1000, 18]]);
        $this->placeOrder([['menu_id' => $menu->id, 'qty' => 2]])->assertCreated();
        $order = Order::firstOrFail();
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->actingAsRole(UserRole::Barista);
        $this->postJson(self::API.'/station/barista/tickets/'.$order->tickets()->first()->id.'/start')->assertOk();

        $this->actingAsRole(UserRole::Admin);
        $this->postJson(self::API."/orders/{$order->id}/cancel", ['reason' => 'Salah pesan', 'restore_stock' => true])->assertOk()->assertJsonPath('data.status.code', 'dibatalkan');
        $this->assertSame('refunded', $order->payments()->first()->status->value);
        $this->assertSame('1000.000', Ingredient::where('name', 'Kopi')->value('stock'));
        $this->assertSame(1, StockMovement::where('type', 'pembalikan_pemakaian')->count());
    }
}
