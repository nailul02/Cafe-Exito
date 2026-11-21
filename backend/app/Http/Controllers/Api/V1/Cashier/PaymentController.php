<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectPaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    private const WITH = ['table', 'details.cancellations.refundItem.refundRequest', 'payment.verifier', 'payment.latestProof', 'payments.proofs', 'tickets'];

    public function __construct(private readonly PaymentService $payments) {}

    public function verify(VerifyPaymentRequest $request, Payment $payment)
    {
        $order = $this->payments->verify($payment, $request->user(), $request->validated('method'), $request->validated('amount_received'));

        return ApiResponse::success(OrderResource::make($order->load(self::WITH)), 'Pembayaran terverifikasi');
    }

    public function reject(RejectPaymentRequest $request, Payment $payment)
    {
        $order = $this->payments->reject($payment, $request->user(), $request->validated('reason'));

        return ApiResponse::success(OrderResource::make($order->load(self::WITH)), 'Pembayaran ditolak');
    }

    public function proof(Payment $payment): StreamedResponse
    {
        abort_unless($payment->order()->where('order_source', 'qr_customer')->exists(), 404);
        $proof = $payment->proofs()->latest('id')->firstOrFail();
        abort_unless(Storage::disk('local')->exists($proof->path), 404);
        return Storage::disk('local')->response($proof->path, 'qris-proof-'.$proof->id, [
            'Content-Type' => $proof->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
