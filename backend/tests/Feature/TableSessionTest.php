<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableSession;
use Illuminate\Support\Str;

class TableSessionTest extends ApiTestCase
{
    public function test_qr_token_opens_one_session_and_recovers_it_across_devices(): void
    {
        $table = $this->table('1');
        $this->getJson(self::API.'/customer/tables/1')->assertStatus(422);
        $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token])->assertOk()
            ->assertJsonPath('data.requires_name', true);

        $first = $this->postJson(self::API.'/customer/sessions/open', [
            'qr_token' => $table->qr_token, 'customer_name' => 'Ayu',
        ])->assertOk()->json('data');
        $second = $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token])
            ->assertOk()->json('data');

        $this->assertNotSame($first['session_token'], $second['session_token']);
        $this->assertSame('Ayu', $second['session']['customer_name']);
        $this->assertSame(1, \App\Models\TableSession::query()->where('table_id', $table->id)->where('status', 'active')->count());
        $this->getJson(self::API.'/customer/sessions/current', ['X-Table-Session' => $second['session_token']])
            ->assertOk()->assertJsonPath('data.table.number', '1')->assertJsonPath('data.customer_name', 'Ayu');
    }

    public function test_checkout_retry_is_idempotent_and_new_items_append_to_processing_order(): void
    {
        $table = $this->table('1');
        $menu = $this->menuWithRecipe('Sesi Kopi', ['Biji Kopi' => [1000, 10]], false, 12000);
        $opened = $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token, 'customer_name' => 'Budi'])->assertOk();
        $headers = ['X-Table-Session' => $opened->json('data.session_token')];
        $body = ['payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]], 'client_request_id' => (string) Str::uuid()];

        $first = $this->postJson(self::API.'/customer/sessions/orders', $body, $headers)->assertCreated();
        $retry = $this->postJson(self::API.'/customer/sessions/orders', $body, $headers)->assertCreated();
        $this->assertSame($first->json('data.public_token'), $retry->json('data.public_token'));
        $order = Order::query()->where('public_token', $first->json('data.public_token'))->firstOrFail();
        $order->update(['status' => 'diproses']);
        $firstTicket = $order->tickets()->where('station', 'barista')->firstOrFail();
        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $this->postJson(self::API."/station/barista/tickets/{$firstTicket->id}/start")->assertOk();

        $added = $this->postJson(self::API.'/customer/sessions/orders', [
            ...$body, 'client_request_id' => (string) Str::uuid(),
        ], $headers)->assertCreated();
        $this->assertSame($order->public_token, $added->json('data.public_token'));
        $this->assertSame(24000, $order->fresh()->total);
        $this->assertCount(2, $order->details);
        $session = TableSession::query()->where('table_id', $table->id)->where('status', 'active')->firstOrFail();
        $this->assertSame(1, Order::query()->where('table_session_id', $session->id)->count());

        $secondTicket = $order->tickets()->where('station', 'barista')->where('batch_number', 2)->firstOrFail();

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk()
            ->assertJsonPath('data.status.code', 'diproses');
        $this->assertSame('verified', $order->payment->fresh()->status->value);

        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $this->postJson(self::API."/station/barista/tickets/{$firstTicket->id}/complete")->assertOk();
        $this->postJson(self::API."/station/barista/tickets/{$secondTicket->id}/start")->assertOk();
        $this->postJson(self::API."/station/barista/tickets/{$secondTicket->id}/complete")->assertOk();
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/orders/{$order->id}/complete")->assertOk()->assertJsonPath('data.status.code', 'selesai');
        $newOrder = $this->postJson(self::API.'/customer/sessions/orders', [
            ...$body, 'client_request_id' => (string) Str::uuid(),
        ], $headers)->assertCreated();
        $this->assertNotSame($order->public_token, $newOrder->json('data.public_token'));
        $session = TableSession::query()->where('table_id', $table->id)->where('status', 'active')->firstOrFail();
        $this->assertSame(2, Order::query()->where('table_session_id', $session->id)->count());
    }

    public function test_customer_cannot_read_an_order_from_another_table_session(): void
    {
        $tableOne = $this->table('1');
        $tableTwo = $this->table('2');
        $menu = $this->menuWithRecipe('Sesi Terpisah', ['Biji Kopi' => [1000, 10]], false, 12000);
        $firstSession = $this->postJson(self::API.'/customer/sessions/open', [
            'qr_token' => $tableOne->qr_token, 'customer_name' => 'Ayu',
        ])->assertOk()->json('data');
        $order = $this->postJson(self::API.'/customer/sessions/orders', [
            'payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]],
            'client_request_id' => (string) Str::uuid(),
        ], ['X-Table-Session' => $firstSession['session_token']])->assertCreated()->json('data');
        $secondSession = $this->postJson(self::API.'/customer/sessions/open', [
            'qr_token' => $tableTwo->qr_token, 'customer_name' => 'Budi',
        ])->assertOk()->json('data');

        $this->getJson(self::API.'/customer/orders/'.$order['public_token'], [
            'X-Table-Session' => $secondSession['session_token'],
        ])->assertNotFound();
        $this->getJson(self::API.'/customer/orders/'.$order['public_token'], [
            'X-Table-Session' => $firstSession['session_token'],
        ])->assertOk();
    }

    public function test_only_cashier_can_close_a_fully_paid_completed_session(): void
    {
        $table = $this->table('1');
        $opened = $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token, 'customer_name' => 'Sari'])->assertOk();
        $sessionId = \App\Models\TableSession::query()->where('table_id', $table->id)->value('id');
        $this->actingAsRole(UserRole::Manager);
        $this->postJson(self::API."/cashier/sessions/$sessionId/close")->assertForbidden();

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/cashier/sessions/$sessionId/close")
            ->assertOk()->assertJsonPath('data.status', 'closed')->assertJsonPath('data.balance', 0);
    }

    public function test_refund_after_session_close_keeps_the_session_closed_and_old_token_revoked(): void
    {
        $table = $this->table('1');
        $menu = $this->menuWithRecipe('Sesi selesai', ['Biji kopi sesi' => [100, 1]], false, 20000);
        $opened = $this->postJson(self::API.'/customer/sessions/open', [
            'qr_token' => $table->qr_token, 'customer_name' => 'Pelanggan Sesi',
        ])->assertOk()->json('data');
        $sessionToken = $opened['session_token'];
        $headers = ['X-Table-Session' => $sessionToken];
        $placed = $this->postJson(self::API.'/customer/sessions/orders', [
            'payment_method' => 'tunai',
            'items' => [['menu_id' => $menu->id, 'qty' => 1]],
            'client_request_id' => (string) Str::uuid(),
        ], $headers)->assertCreated();
        $order = Order::query()->where('public_token', $placed->json('data.public_token'))->firstOrFail();
        $sessionId = \App\Models\TableSession::query()->where('table_id', $table->id)->value('id');
        $order->update(['status' => 'selesai']);

        $cashier = $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/cashier/sessions/$sessionId/close")->assertOk()->assertJsonPath('data.status', 'closed');

        $refundId = $this->postJson(self::API.'/refunds', [
            'payment_id' => $order->payment->id, 'amount' => 5000, 'reason' => 'Refund setelah sesi ditutup',
        ])->assertCreated()->json('data.id');
        $this->actingAsRole(UserRole::Owner);
        $this->postJson(self::API."/refunds/$refundId/decision", ['decision' => 'approved'])->assertOk();
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API."/refunds/$refundId/execute", ['method' => 'cash', 'confirm_cash' => true])->assertOk();

        $this->assertSame('closed', \App\Models\TableSession::findOrFail($sessionId)->status);
        $this->assertNotNull(\App\Models\TableSessionToken::query()->where('table_session_id', $sessionId)->value('revoked_at'));
        $this->getJson(self::API.'/customer/sessions/current', $headers)->assertUnauthorized();
        $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token])
            ->assertOk()->assertJsonPath('data.requires_name', true);
    }
}
