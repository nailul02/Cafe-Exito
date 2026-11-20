<?php
namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItemCancellation;
use App\Models\RefundRequest;
use App\Models\User;

class CustomerPartialCancellationTest extends ApiTestCase
{
    public function test_customer_can_cancel_partial_quantity_on_pending_cash_order_and_bill_reduces(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>3]],'tunai')->assertCreated()->json('data.public_token');
        $order=Order::where('public_token',$token)->firstOrFail();$detail=$order->details()->firstOrFail();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Berubah rencana'])->assertOk()
            ->assertJsonPath('data.total',40000)->assertJsonPath('data.items.0.qty_remaining',2);
        $this->assertSame(40000,$order->refresh()->total);
        $this->assertSame(40000,$order->payment->amount);
        $this->assertSame('pending',$order->payment->status->value);
        $this->assertDatabaseHas('order_item_cancellations',['order_detail_id'=>$detail->id,'qty'=>1,'actor_type'=>'customer','reason'=>'Berubah rencana']);
        $this->assertDatabaseCount('refund_requests',0);
    }

    public function test_paid_customer_cancellations_merge_into_open_refund_request(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>2]],'qris')->assertCreated()->json('data.public_token');
        $this->uploadPaymentProof($token);
        $order=Order::where('public_token',$token)->firstOrFail();$detail=$order->details()->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Salah pilih'])->assertOk();
        $this->assertSame('verified',$order->payment->refresh()->status->value);
        $this->assertSame(20000,RefundRequest::firstOrFail()->amount);
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Pesanan tidak diperlukan'])->assertOk()
            ->assertJsonPath('data.status.code','dibatalkan');
        $this->assertDatabaseCount('refund_requests',1);
        $refund=RefundRequest::firstOrFail();$this->assertSame(40000,$refund->amount);$this->assertSame(2,$refund->items()->count());
        $this->assertSame('verified',$order->payment->refresh()->status->value);
    }

    public function test_started_item_cannot_be_cancelled_but_other_station_item_can(): void
    {
        $this->table();$drink=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);$food=$this->menuWithRecipe('Toast',['Flour'=>[1000,1]],true,15000);
        $token=$this->placeOrder([['menu_id'=>$drink->id,'qty'=>1],['menu_id'=>$food->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');
        $this->uploadPaymentProof($token);
        $order=Order::where('public_token',$token)->firstOrFail();$this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $drinkDetail=$order->details()->where('station','barista')->firstOrFail();$foodDetail=$order->details()->where('station','kitchen')->firstOrFail();
        $this->actingAsRole(UserRole::BaristaKitchen,['station'=>'barista']);$ticket=$order->tickets()->where('station','barista')->firstOrFail();
        $this->postJson(self::API."/station/barista/tickets/{$ticket->id}/items/{$drinkDetail->id}/start")->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$drinkDetail->id,'qty'=>1,'reason'=>'Batal'])->assertStatus(409);
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$foodDetail->id,'qty'=>1,'reason'=>'Batal makanan'])->assertOk();
        $this->assertSame(1,OrderItemCancellation::count());
    }

    public function test_manager_cannot_use_owner_item_cancellation_endpoint(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]]);$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]])->assertCreated();$o=Order::firstOrFail();
        $this->actingAsRole(UserRole::Manager);$this->postJson(self::API."/orders/{$o->id}/items/cancel",['detail_id'=>$o->details->first()->id,'qty'=>1,'reason'=>'Owner action'])->assertForbidden();
    }

    public function test_idempotent_customer_cancellation_replay_does_not_duplicate_cancellation_or_refund(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>2]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();$detail=$order->details->first();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $payload=['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Payload konsisten'];
        $first=$this->postJson(self::API."/customer/orders/$token/cancel",$payload,['Idempotency-Key'=>'customer-cancel-1'])->assertOk();
        $replay=$this->postJson(self::API."/customer/orders/$token/cancel",$payload,['Idempotency-Key'=>'customer-cancel-1'])->assertOk();
        $this->assertSame($first->json('data'),$replay->json('data'), 'Replay harus mengembalikan representasi pesanan yang konsisten.');
        $this->assertDatabaseCount('order_item_cancellations',1);$this->assertDatabaseCount('refund_requests',1);$this->assertDatabaseCount('refund_request_items',1);
        $this->assertSame(20000,RefundRequest::firstOrFail()->amount);
    }

    public function test_reusing_customer_idempotency_key_with_different_payload_is_rejected(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>3]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();$detail=$order->details->first();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Awal'],['Idempotency-Key'=>'customer-cancel-2'])->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$detail->id,'qty'=>2,'reason'=>'Payload berubah'],['Idempotency-Key'=>'customer-cancel-2'])->assertStatus(409)
            ->assertJsonPath('error.code','IDEMPOTENCY_KEY_REUSED');
        $this->assertDatabaseCount('order_item_cancellations',1);$this->assertDatabaseCount('refund_requests',1);$this->assertSame(20000,RefundRequest::firstOrFail()->amount);
    }

}
