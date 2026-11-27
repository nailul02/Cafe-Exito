<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\PaymentProof;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class QrisPaymentProofTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function qrisOrder(): array
    {
        $menu = $this->menuWithRecipe('QR Test', ['Teh test' => [10, 1]], false, 5000);
        $response = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'qris', '1', [], false)->assertCreated();
        return [Order::query()->where('public_token', $response->json('data.public_token'))->firstOrFail(), $response->json('data.public_token')];
    }

    private function png(string $name = 'proof.png'): UploadedFile { return UploadedFile::fake()->image($name, 30, 30); }

    public function test_customer_qris_cannot_be_verified_or_rejected_without_pending_proof_or_changed_to_cash(): void
    {
        [$order] = $this->qrisOrder();
        $payment = $order->payment;
        $this->actingAsRole(UserRole::Kasir);

        $this->postJson(self::API.'/cashier/payments/'.$payment->id.'/verify')->assertUnprocessable();
        $this->postJson(self::API.'/cashier/payments/'.$payment->id.'/verify', ['method' => 'tunai'])->assertUnprocessable();
        $this->postJson(self::API.'/cashier/payments/'.$payment->id.'/reject', ['reason' => 'Bukti tidak tersedia'])->assertUnprocessable();

        $this->assertSame('pending', $payment->fresh()->status->value);
        $this->assertSame('qris', $payment->fresh()->method->value);
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status->value);
        $this->assertSame(0, $order->tickets()->count());
    }

    public function test_qr_self_order_proof_upload_is_private_unique_and_verification_creates_ticket_once(): void
    {
        [$order, $token] = $this->qrisOrder();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $this->png()])->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonMissingPath('data.path');
        $proof = $order->payment->proofs()->latest('id')->firstOrFail();
        $freshOrder = $order->fresh();
        $this->assertSame('pending', $freshOrder->payment->status->value);
        $this->assertSame('pending', $proof->status);
        $this->assertNull($freshOrder->paid_at);
        $this->assertSame('menunggu_pembayaran', $freshOrder->status->value);
        $this->assertSame(0, $freshOrder->tickets()->count());
        $this->assertSame(64, strlen($proof->sha256));
        Storage::disk('local')->assertExists($proof->path);
        $this->assertSame(0, $order->tickets()->count());

        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/cashier/payments/'.$order->payment->id.'/proof')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify', ['method' => 'tunai'])->assertUnprocessable();
        $this->assertSame('pending', $order->payment->fresh()->status->value);
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk()->assertJsonPath('data.status.code', 'dibayar');
        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertStatus(409);
        $this->assertSame(1, $order->tickets()->count());
        $paymentId = $order->payment->id;
        $actions = AuditLog::query()->where(function ($query) use ($proof, $paymentId) {
            $query->where(function ($proofQuery) use ($proof) {
                $proofQuery->where('subject_type', 'payment_proof')->where('subject_id', $proof->id);
            })->orWhere(function ($paymentQuery) use ($paymentId) {
                $paymentQuery->where('subject_type', 'payment')->where('subject_id', $paymentId);
            });
        })->orderBy('id')->pluck('action')->all();
        $this->assertSame(['payment_proof.uploaded', 'payment_proof.verified', 'payment.verified'], $actions);
    }

    public function test_upload_rejects_invalid_mime_oversize_and_identical_file(): void
    {
        [, $token] = $this->qrisOrder();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => UploadedFile::fake()->create('x.gif', 10, 'image/gif')])->assertUnprocessable();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => UploadedFile::fake()->create('large.png', 3073, 'image/png')])->assertUnprocessable();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => UploadedFile::fake()->createWithContent('fake.png', 'not an image')])->assertUnprocessable();
        $image = $this->png();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $image])->assertCreated();
        $cashier = $this->actingAsRole(UserRole::Kasir);
        $payment = Order::where('public_token', $token)->firstOrFail()->payment;
        $this->postJson(self::API.'/cashier/payments/'.$payment->id.'/reject', ['reason' => 'Nominal tidak cocok'])->assertOk();
        $this->assertSame('menunggu_pembayaran', Order::where('public_token', $token)->firstOrFail()->status->value);
        $this->assertSame('rejected', $payment->fresh()->status->value);
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $image])->assertUnprocessable();
    }

    public function test_rejected_proof_can_be_replaced_reason_is_required_and_history_remains(): void
    {
        [$order, $token] = $this->qrisOrder();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $this->png('one.png')])->assertCreated();
        $payment = $order->payment;
        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$payment->id.'/reject', [])->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->postJson(self::API.'/cashier/payments/'.$payment->id.'/reject', ['reason' => 'Bukti tidak jelas'])->assertOk();
        $this->getJson(self::API.'/customer/orders/'.$token)->assertOk()->assertJsonPath('data.payment.proof.status', 'rejected')->assertJsonPath('data.payment.proof.reject_reason', 'Bukti tidak jelas');
        $second = $this->png('two.png');
        file_put_contents($second->getRealPath(), "different-content\n", FILE_APPEND);
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $second])->assertCreated();
        $proofs = PaymentProof::query()->whereIn('payment_id', $order->payments()->select('id'))->orderBy('id')->get();
        $this->assertCount(2, $proofs);
        $this->assertSame('rejected', $proofs->first()->status);
        $this->assertSame('pending', $proofs->last()->status);
        $this->assertSame(1, $order->payments()->where('status', 'pending')->count());
        $this->assertSame(1, $order->payments()->where('status', 'rejected')->count());
    }

    public function test_partial_qris_installment_uses_a_new_proof_and_releases_production_once_fully_paid(): void
    {
        $menu = $this->menuWithRecipe('QR cicilan', ['Teh cicilan' => [100, 1]], false, 20000);
        $token = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 2]], 'qris', '1', [], false)->assertCreated()->json('data.public_token');
        $firstProofFile = $this->png('first-installment.png');
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $firstProofFile])->assertCreated();
        $order = Order::query()->where('public_token', $token)->firstOrFail();
        $first = $order->payments()->oldest('id')->firstOrFail();
        $firstProof = $first->proofs()->where('status', 'pending')->firstOrFail();

        $this->actingAsRole(UserRole::Kasir);
        $this->postJson(self::API.'/cashier/payments/'.$first->id.'/verify', ['amount_applied' => 20000])->assertOk();
        $this->assertSame('verified', $first->fresh()->status->value);
        $this->assertSame('verified', $firstProof->fresh()->status);
        $this->assertSame(20000, (int) $first->orderDetails()->sum('payment_order_details.amount'));
        $this->assertSame(0, $order->tickets()->count(), 'A partial QRIS payment must not release production.');

        $remainder = $order->payments()->where('status', 'pending')->firstOrFail();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $firstProofFile])->assertUnprocessable();
        $remainderProof = $this->png('remainder.png');
        file_put_contents($remainderProof->getRealPath(), "second installment\n", FILE_APPEND);
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $remainderProof])->assertCreated();
        $this->postJson(self::API.'/cashier/payments/'.$remainder->id.'/verify')->assertOk();

        $this->assertSame('dibayar', $order->fresh()->status->value);
        $this->assertSame(1, $order->tickets()->count());
        $this->assertSame(2, $order->payments()->where('status', 'verified')->count());
        $this->postJson(self::API.'/cashier/payments/'.$first->id.'/verify')->assertStatus(409);
        $this->assertSame(1, $order->tickets()->count(), 'A verified installment retry must not duplicate production tickets.');
    }

    public function test_tokens_are_required_proof_access_is_protected_and_other_order_token_isolated(): void
    {
        [, $token] = $this->qrisOrder();
        $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $this->png()])->assertCreated();
        $payment = Order::where('public_token', $token)->firstOrFail()->payment;
        $this->get(self::API.'/cashier/payments/'.$payment->id.'/proof')->assertUnauthorized();
        $this->post(self::API.'/customer/orders/not-a-valid-public-token/payment-proof', ['proof' => $this->png('other.png')])->assertNotFound();
        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'barista']);
        $this->get(self::API.'/cashier/payments/'.$payment->id.'/proof')->assertForbidden();
    }
}
