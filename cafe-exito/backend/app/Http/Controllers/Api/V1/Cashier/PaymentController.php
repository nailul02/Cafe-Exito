<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectPaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Support\ApiResponse;

class PaymentController extends Controller
{
    private const WITH = ['table', 'details', 'payment.verifier', 'tickets'];

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
}
