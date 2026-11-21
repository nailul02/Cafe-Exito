<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * POS Kasir: pesanan dibuat dengan status menunggu_pembayaran.
 * Pembayaran diverifikasi secara manual oleh kasir (tidak otomatis).
 * Keputusan bisnis: tunai dan QRIS sama-sama menunggu konfirmasi kasir.
 */
class CashierPosTest extends ApiTestCase
{
    public function test_nominal_tunai_tidak_diterima_saat_order_pos_dibuat(): void
    {
        $menu = $this->menuWithRecipe('Teh POS', ['Teh POS' => [20, 2]], false, 12000);
        $this->actingAsRole(UserRole::Kasir);

        $this->postJson(self::API.'/cashier/pos/orders', [
            'client_request_id' => (string) Str::uuid(),
            'service_type' => 'takeaway',
            'payment_method' => 'tunai',
            'amount_received' => 20000,
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('amount_received');

        $this->assertSame(0, Order::count());
    }

    public function test_kasir_dapat_membuat_pesanan_dine_in_tunai_dan_retry_tidak_menggandakan(): void
    {
        $table = $this->table('7');
        $menu = $this->menuWithRecipe('Latte POS', ['Kopi POS' => [100, 18]], false, 20000);
        $this->actingAsRole(UserRole::Kasir);

        $payload = [
            'client_request_id' => (string) Str::uuid(),
            'service_type' => 'dine_in',
            'table_number' => $table->table_number,
            'customer_name' => 'Uji POS',
            'payment_method' => 'tunai',
            'items' => [['menu_id' => $menu->id, 'qty' => 2, 'price' => 1]],
        ];

        $this->getJson(self::API.'/cashier/tables')->assertOk()->assertJsonPath('data.0.table_number', '7');
        $response = $this->postJson(self::API.'/cashier/pos/orders', $payload)
            ->assertCreated()
            // POS membuat pesanan menunggu_pembayaran; kasir verifikasi secara manual
            ->assertJsonPath('data.status.code', 'menunggu_pembayaran')
            ->assertJsonPath('data.total', 40000)
            ->assertJsonPath('data.payment.status.code', 'pending')
            ->assertJsonPath('data.payment.amount_received', null)
            ->assertJsonPath('data.table.id', $table->id);

        $orderId = $response->json('data.id');
        $this->assertSame(1, Order::findOrFail($orderId)->tickets()->count());
        $this->assertSame(1, Order::query()->where('order_source', 'pos_cashier')->count());

        // Retry dengan client_request_id yang sama tidak menggandakan pesanan
        $this->postJson(self::API.'/cashier/pos/orders', $payload)
            ->assertCreated()->assertJsonPath('data.id', $orderId);
        $this->assertSame(1, Order::query()->where('order_source', 'pos_cashier')->count());

        // Kasir memverifikasi pembayaran secara manual
        $order = Order::findOrFail($orderId);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify', ['amount_received' => 50000])
            ->assertOk()
            ->assertJsonPath('data.status.code', 'dibayar')
            ->assertJsonPath('data.payment.status.code', 'verified')
            ->assertJsonPath('data.payment.change', 10000);

        $this->assertSame(1, $order->tickets()->count());
    }

    public function test_order_tunai_dapat_selesai_saat_pending_dan_pelunasan_terlambat_dicatat_kasir(): void
    {
        $menu = $this->menuWithRecipe('Kopi Tunai Tertunda', ['Kopi Tunai' => [100, 18]], false, 20000);
        $this->actingAsRole(UserRole::Kasir);
        $response = $this->postJson(self::API.'/cashier/pos/orders', [
            'client_request_id' => (string) Str::uuid(),
            'service_type' => 'takeaway',
            'payment_method' => 'tunai',
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ])->assertCreated()->assertJsonPath('data.payment.status.code', 'pending');

        $order = Order::query()->findOrFail($response->json('data.id'));
        $payment = $order->payment;
        $this->assertSame(1, $order->tickets()->count());
        $this->postJson(self::API."/cashier/payments/{$payment->id}/reject", ['reason' => 'Uang tunai belum diterima'])
            ->assertUnprocessable();
        $this->assertSame('pending', $payment->fresh()->status->value, 'Pembayaran tunai tetap pending sampai kasir mencatat uang diterima.');

        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $ticket = $order->tickets()->firstOrFail();
        $this->getJson(self::API.'/station/barista/tickets')->assertOk()->assertJsonFragment(['id' => $ticket->id]);
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/start")->assertOk();
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/complete")->assertOk();
        $this->assertSame('pending', $payment->fresh()->status->value, 'Penyelesaian tiket tidak melunasi pembayaran.');

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/orders/{$order->id}/complete")
            ->assertOk()->assertJsonPath('data.status.code', 'selesai')->assertJsonPath('data.payment.status.code', 'pending');
        $this->assertSame('pending', $payment->fresh()->status->value, 'Pesanan selesai tetap menjadi tunggakan sampai kasir menerima uang.');
        $this->getJson(self::API.'/cashier/dashboard')->assertOk()->assertJsonPath('data.awaiting_payment', 1);
        $this->getJson(self::API.'/cashier/orders?filter[status]=menunggu_pembayaran')
            ->assertOk()->assertJsonPath('data.0.id', $order->id)->assertJsonPath('data.0.payment.status.code', 'pending');

        $this->actingAsRole(UserRole::Manager);
        $this->postJson(self::API."/cashier/payments/{$payment->id}/verify", ['amount_received' => 20000])->assertForbidden();
        $this->assertSame('pending', $payment->fresh()->status->value, 'Pengguna tanpa izin kasir tidak dapat melunasi pembayaran.');

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/payments/{$payment->id}/verify", ['amount_received' => 20000])
            ->assertOk()->assertJsonPath('data.status.code', 'selesai')->assertJsonPath('data.payment.status.code', 'verified');
        $this->assertSame('verified', $payment->fresh()->status->value);
        $this->assertSame('selesai', $order->fresh()->status->value, 'Pelunasan tidak mengubah status selesai menjadi status lain.');
    }

    public function test_kasir_dapat_membuat_takeaway_dengan_qris_statis_tanpa_meja(): void
    {
        $menu = $this->menuWithRecipe('Teh POS', ['Teh POS' => [20, 2]], false, 12000);
        $this->actingAsRole(UserRole::Kasir);

        $response = $this->postJson(self::API.'/cashier/pos/orders', [
            'client_request_id' => (string) Str::uuid(),
            'service_type' => 'takeaway',
            'table_number' => null,
            'customer_name' => 'Pelanggan Takeaway',
            'payment_method' => 'qris',
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ])->assertCreated()
            ->assertJsonPath('data.service_type', 'takeaway')
            ->assertJsonPath('data.table', null)
            ->assertJsonPath('data.payment.method.code', 'qris')
            // QRIS POS menunggu pemeriksaan dan konfirmasi manual Kasir tanpa upload bukti Self-Order.
            ->assertJsonPath('data.payment.status.code', 'pending');

        // Verifikasi QRIS oleh kasir
        $orderId = $response->json('data.id');
        $order = Order::findOrFail($orderId);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')
            ->assertOk()
            ->assertJsonPath('data.payment.status.code', 'verified')
            ->assertJsonPath('data.status.code', 'dibayar');
    }

    public function test_kasir_tidak_dapat_mengubah_qris_pos_menjadi_tunai_saat_verifikasi(): void
    {
        $menu = $this->menuWithRecipe('Teh POS QRIS tidak dapat diganti', ['Teh POS QRIS' => [20, 2]], false, 12000);
        $this->actingAsRole(UserRole::Kasir);

        $response = $this->postJson(self::API.'/cashier/pos/orders', [
            'client_request_id' => (string) Str::uuid(),
            'service_type' => 'takeaway',
            'payment_method' => 'qris',
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ])->assertCreated();

        $order = Order::findOrFail($response->json('data.id'));
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify', ['method' => 'tunai'])
            ->assertUnprocessable();

        $this->assertSame('pending', $order->payment->fresh()->status->value);
        $this->assertSame('qris', $order->payment->fresh()->method->value);
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status->value);
        $this->assertSame(0, AuditLog::query()
            ->where('action', 'payment.verified')
            ->where('subject_type', 'payment')
            ->where('subject_id', $order->payment->id)
            ->count());
    }

    public function test_kasir_dapat_membuat_dine_in_tanpa_meja_di_pos(): void
    {
        $menu = $this->menuWithRecipe('Kopi Bar POS', ['Kopi Bar' => [20, 2]], false, 15000);
        $this->actingAsRole(UserRole::Kasir);

        $response = $this->postJson(self::API.'/cashier/pos/orders', [
            'client_request_id' => (string) Str::uuid(),
            'service_type' => 'dine_in',
            'table_number' => null,
            'customer_name' => 'Pelanggan Bar Counter',
            'payment_method' => 'tunai',
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
        ])->assertCreated()
            ->assertJsonPath('data.service_type', 'dine_in')
            ->assertJsonPath('data.table', null)
            ->assertJsonPath('data.customer_name', 'Pelanggan Bar Counter')
            ->assertJsonPath('data.payment.status.code', 'pending');

        // Verifikasi tunai oleh kasir
        $orderId = $response->json('data.id');
        $order = Order::findOrFail($orderId);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify', ['amount_received' => 20000])
            ->assertOk()
            ->assertJsonPath('data.payment.status.code', 'verified');
    }

    public function test_endpoint_bukti_qr_self_order_menolak_order_qris_pos(): void
    {
        $menu = $this->menuWithRecipe('Teh POS QR proof', ['Teh' => [20, 2]], false, 12000);
        $this->actingAsRole(UserRole::Kasir);
        $response = $this->postJson(self::API.'/cashier/pos/orders', ['client_request_id' => (string) Str::uuid(), 'service_type' => 'takeaway', 'payment_method' => 'qris', 'items' => [['menu_id' => $menu->id, 'qty' => 1]]])->assertCreated();
        $order = Order::findOrFail($response->json('data.id'));
        $this->post(self::API.'/customer/orders/'.$order->public_token.'/payment-proof', ['proof' => UploadedFile::fake()->image('pos.png', 20, 20)])->assertUnprocessable();
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk()->assertJsonPath('data.payment.status.code', 'verified');
    }

    public function test_pos_ditolak_untuk_pengguna_tanpa_izin_kasir(): void
    {
        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $this->getJson(self::API.'/cashier/tables')->assertForbidden();
        $this->postJson(self::API.'/cashier/pos/orders', [])->assertForbidden();
    }
}
