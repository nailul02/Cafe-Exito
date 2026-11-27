<?php
namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RefundWorkflowTest extends ApiTestCase
{
    public function test_owner_approval_then_cashier_execution_is_audited_and_idempotent(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');
        $this->uploadPaymentProof($token);
        $order=Order::where('public_token',$token)->firstOrFail();$detail=$order->details()->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Pelanggan batal'])->assertOk();
        $refund=RefundRequest::query()->whereIn('payment_id', $order->payments()->select('id'))->latest('id')->firstOrFail();
        $this->actingAsRole(UserRole::Manager);$this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertForbidden();
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertOk()->assertJsonPath('data.status','approved');
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API."/refunds/{$refund->id}/execute",['method'=>'cash','confirm_cash'=>true])->assertOk()->assertJsonPath('data.status','completed');
        $this->assertSame('refunded',$order->payment->refresh()->status->value);
        $this->assertDatabaseHas('refund_executions',['refund_request_id'=>$refund->id,'amount'=>20000,'method'=>'cash']);
        $this->postJson(self::API."/refunds/{$refund->id}/execute",['method'=>'cash','confirm_cash'=>true])->assertStatus(409);
    }

    public function test_transfer_requires_reference_and_private_proof(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$o=Order::where('public_token',$token)->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$o->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$o->details->first()->id,'qty'=>1,'reason'=>'Batal'])->assertOk();$refund=RefundRequest::query()->whereIn('payment_id', $o->payments()->select('id'))->latest('id')->firstOrFail();
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertOk();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API."/refunds/{$refund->id}/execute",['method'=>'bank_transfer'])->assertUnprocessable();
    }

    public function test_owner_cannot_decide_refund_for_order_created_by_that_owner(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$order->details->first()->id,'qty'=>1,'reason'=>'Batal'])->assertOk();$refund=RefundRequest::query()->whereIn('payment_id', $order->payments()->select('id'))->latest('id')->firstOrFail();
        User::factory()->create(['role'=>UserRole::Manager,'is_active'=>true]);
        $owner=$this->actingAsRole(UserRole::Owner);$order->update(['created_by'=>$owner->id]);
        $this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertOk()->assertJsonPath('data.status','conflict_of_interest');
        $this->assertDatabaseHas('app_notifications',['type'=>'refund.conflict_of_interest']);
    }

    public function test_cashier_can_withdraw_only_a_manual_request_they_created(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();
        $cashier=$this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $id=$this->postJson(self::API.'/refunds',['payment_id'=>$order->payment->id,'amount'=>10000,'reason'=>'Penyesuaian transaksi'])->assertCreated()->json('data.id');
        $this->postJson(self::API."/refunds/$id/withdraw",['reason'=>'Pengajuan tidak diperlukan'])->assertOk()->assertJsonPath('data.status','withdrawn');
        $this->assertSame($cashier->id,RefundRequest::findOrFail($id)->withdrawn_by);
    }

    public function test_manual_request_is_rejected_when_payment_already_has_pending_cancellation_refund(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>2]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();
        $cashier=$this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->postJson(self::API."/customer/orders/$token/cancel",['detail_id'=>$order->details->first()->id,'qty'=>1,'reason'=>'Pelanggan batal'])->assertOk();
        $this->actingAs($cashier)->postJson(self::API.'/refunds',['payment_id'=>$order->payment->id,'amount'=>5000,'reason'=>'Manual'])->assertStatus(409)
            ->assertJsonPath('error.code','REFUND_REQUEST_ALREADY_PENDING')->assertJsonPath('message','Payment ini sudah memiliki refund request yang menunggu keputusan Owner.');
        $this->assertSame(1, RefundRequest::query()->whereIn('payment_id', $order->payments()->select('id'))->count());
    }

    public function test_owner_can_cancel_a_partial_item_and_create_an_owner_attributed_refund(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>3]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $owner=$this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/orders/{$order->id}/items/cancel",['detail_id'=>$order->details->first()->id,'qty'=>1,'reason'=>'Owner adjustment'],['Idempotency-Key'=>'owner-cancel-1'])->assertOk();
        $this->assertDatabaseHas('order_item_cancellations',['order_id'=>$order->id,'qty'=>1,'source'=>'owner_cancellation','actor_type'=>'owner','actor_id'=>$owner->id]);
        $this->assertDatabaseHas('refund_requests',['payment_id'=>$order->payment->id,'amount'=>20000,'source'=>'owner_cancellation','requested_by'=>$owner->id]);
    }

    public function test_partial_refund_keeps_payment_verified_and_cumulative_refund_cannot_exceed_payment(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $first=$this->postJson(self::API.'/refunds',['payment_id'=>$order->payment->id,'amount'=>10000,'reason'=>'Refund parsial'])->assertCreated()->json('data.id');
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/$first/decision",['decision'=>'approved'])->assertOk();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API."/refunds/$first/execute",['method'=>'cash','confirm_cash'=>true])->assertOk();
        $this->assertSame('verified',$order->payment->refresh()->status->value);
        $this->postJson(self::API.'/refunds',['payment_id'=>$order->payment->id,'amount'=>11000,'reason'=>'Melebihi sisa'])->assertStatus(409)->assertJsonPath('error.code','REFUND_LIMIT_EXCEEDED');
        $second=$this->postJson(self::API.'/refunds',['payment_id'=>$order->payment->id,'amount'=>10000,'reason'=>'Sisa refund'])->assertCreated()->json('data.id');
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/$second/decision",['decision'=>'approved'])->assertOk();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API."/refunds/$second/execute",['method'=>'cash','confirm_cash'=>true])->assertOk();
        $this->assertSame('refunded',$order->payment->refresh()->status->value);
    }

    public function test_revoked_refund_does_not_repeat_same_idempotent_cancellation_but_other_quantity_can_be_cancelled(): void
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>2]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();$detail=$order->details->first();
        $this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $payload=['detail_id'=>$detail->id,'qty'=>1,'reason'=>'Pelanggan batal'];
        $this->postJson(self::API."/customer/orders/$token/cancel",$payload,['Idempotency-Key'=>'cancel-unit-1'])->assertOk();$first=RefundRequest::query()->whereIn('payment_id', $order->payments()->select('id'))->latest('id')->firstOrFail();
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/{$first->id}/decision",['decision'=>'approved'])->assertOk();
        $this->postJson(self::API."/refunds/{$first->id}/revoke",['reason'=>'Perlu pemeriksaan'])->assertOk()->assertJsonPath('data.status','approval_revoked');
        $this->postJson(self::API."/customer/orders/$token/cancel",$payload,['Idempotency-Key'=>'cancel-unit-1'])->assertOk();
        $this->assertSame(1, \App\Models\OrderItemCancellation::query()->where('order_detail_id', $detail->id)->count());
        $this->assertSame(1, \App\Models\RefundRequestItem::query()->where('cancellation_id', \App\Models\OrderItemCancellation::query()->where('order_detail_id', $detail->id)->value('id'))->count());
        $this->postJson(self::API."/customer/orders/$token/cancel",$payload,['Idempotency-Key'=>'cancel-unit-2'])->assertOk();
        $cancellationIds = \App\Models\OrderItemCancellation::query()->where('order_detail_id', $detail->id)->pluck('id');
        $refundIds = RefundRequest::query()->whereIn('payment_id', $order->payments()->select('id'))->pluck('id');
        $this->assertCount(2, $cancellationIds);
        $this->assertSame(2, \App\Models\RefundRequestItem::query()->whereIn('cancellation_id', $cancellationIds)->count());
        $this->assertSame(2, $refundIds->count());
    }

    public function test_manager_records_conflict_note_owner_verifies_and_manager_cannot_verify_or_decide(): void
    {
        [$refund] = $this->makeVerifiedManualRefund();$refund->update(['status'=>'conflict_of_interest']);
        $noteData=['handled_by'=>'Manager A','resolved_on'=>now()->toDateString(),'outcome'=>'Ditinjau','reason'=>'Dokumentasi pengecualian','amount'=>10000];
        $this->actingAsRole(UserRole::Manager);$noteId=$this->postJson(self::API."/refunds/{$refund->id}/conflict-notes",$noteData)->assertCreated()->json('data.id');
        $this->assertDatabaseHas('refund_conflict_notes',['id'=>$noteId,'verified_at'=>null,'verified_by'=>null]);
        $this->postJson(self::API."/refund-conflict-notes/$noteId/verify")->assertForbidden();
        $this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertForbidden();
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refund-conflict-notes/$noteId/verify")->assertOk();
        $this->assertDatabaseHas('refund_conflict_notes',['id'=>$noteId,'verified_by'=>auth()->id()]);
    }

    public function test_owner_created_conflict_note_is_immediately_verified(): void
    {
        [$refund] = $this->makeVerifiedManualRefund();$refund->update(['status'=>'conflict_of_interest']);
        $owner=$this->actingAsRole(UserRole::Owner);$data=['handled_by'=>'Owner','resolved_on'=>now()->toDateString(),'outcome'=>'Dicatat Owner','reason'=>'Pengecualian dicatat Owner','amount'=>10000];
        $id=$this->postJson(self::API."/refunds/{$refund->id}/conflict-notes",$data)->assertCreated()->json('data.id');
        $this->assertDatabaseHas('refund_conflict_notes',['id'=>$id,'recorded_by'=>$owner->id,'verified_by'=>$owner->id]);
        $this->assertNotNull(\App\Models\RefundConflictNote::findOrFail($id)->verified_at);
    }

    public function test_bank_transfer_execution_uploads_private_proof_and_access_is_owner_cashier_only(): void
    {
        Storage::fake('private');[$refund] = $this->makeVerifiedManualRefund();
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertOk();
        $this->actingAsRole(UserRole::Kasir);$this->post(self::API."/refunds/{$refund->id}/execute",['method'=>'bank_transfer','reference_number'=>'TRX-123','proof'=>UploadedFile::fake()->image('proof.jpg')])->assertOk();
        $refund->refresh();$path=$refund->execution->proof_path;Storage::disk('private')->assertExists($path);
        $this->get(self::API."/refunds/{$refund->id}/proof")->assertOk();
        $this->actingAsRole(UserRole::Owner);$this->get(self::API."/refunds/{$refund->id}/proof")->assertOk();
        $this->actingAsRole(UserRole::Manager);$this->get(self::API."/refunds/{$refund->id}/proof")->assertForbidden();
    }

    public function test_bank_transfer_requires_reference_even_when_proof_is_uploaded(): void
    {
        Storage::fake('private');[$refund] = $this->makeVerifiedManualRefund();
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertOk();
        $this->actingAsRole(UserRole::Kasir);$this->post(self::API."/refunds/{$refund->id}/execute",['method'=>'bank_transfer','proof'=>UploadedFile::fake()->image('proof.jpg')])->assertUnprocessable();
    }

    public function test_deadline_notifications_are_idempotent_across_repeated_command_runs(): void
    {
        [$refund,$cashier] = $this->makeVerifiedManualRefund();
        $refund->forceFill(['created_at'=>now()->subHours(25)])->save();
        $this->artisan('refunds:notify-deadlines')->assertExitCode(0);
        $this->artisan('refunds:notify-deadlines')->assertExitCode(0);
        $this->assertSame(1,\App\Models\AppNotification::where('user_id',$cashier->id)->where('type','refund.decision_overdue')->whereJsonContains('data', ['refund_request_id' => $refund->id])->count());
        $this->assertNotNull($refund->fresh()->decision_late_notified_at);
        $this->actingAsRole(UserRole::Owner);$this->postJson(self::API."/refunds/{$refund->id}/decision",['decision'=>'approved'])->assertOk();
        $refund->update(['decided_at'=>now()->subHours(49)]);
        $this->artisan('refunds:notify-deadlines')->assertExitCode(0);
        $this->artisan('refunds:notify-deadlines')->assertExitCode(0);
        $expectedRecipients = User::query()->where('is_active', true)->whereIn('role', ['owner', 'kasir'])->orderBy('id')->pluck('id')->all();
        $actualRecipients = \App\Models\AppNotification::query()->where('type', 'refund.execution_overdue')
            ->whereJsonContains('data', ['refund_request_id' => $refund->id])->orderBy('user_id')->pluck('user_id')->all();
        $this->assertSame($expectedRecipients, $actualRecipients);
        $this->assertNotNull($refund->fresh()->execution_late_notified_at);
    }

    /** @return array{RefundRequest,User} */
    private function makeVerifiedManualRefund(): array
    {
        $this->table();$menu=$this->menuWithRecipe('Latte',['Beans'=>[1000,1]],false,20000);
        $token=$this->placeOrder([['menu_id'=>$menu->id,'qty'=>1]],'qris')->assertCreated()->json('data.public_token');$this->uploadPaymentProof($token);$order=Order::where('public_token',$token)->firstOrFail();
        $cashier=$this->actingAsRole(UserRole::Kasir);$this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $id=$this->postJson(self::API.'/refunds',['payment_id'=>$order->payment->id,'amount'=>10000,'reason'=>'Refund manual test'])->assertCreated()->json('data.id');
        return [RefundRequest::findOrFail($id),$cashier];
    }
}
