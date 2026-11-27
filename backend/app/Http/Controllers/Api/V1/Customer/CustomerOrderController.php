<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Exceptions\DomainError;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\CancelOrderItemRequest;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Http\Resources\CustomerOrderResource;
use App\Models\Order;
use App\Models\TableSession;
use App\Services\OrderService;
use App\Services\PaymentProofService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Pesanan diakses pelanggan HANYA lewat public_token (ULID acak), bukan ID berurut. */
class CustomerOrderController extends Controller
{
    private const WITH = ['table', 'details.cancellations.refundItems.refundRequest', 'payment.latestProof', 'payments.proofs', 'tickets'];

    public function __construct(private readonly OrderService $orders) {}

    public function store(CreateOrderRequest $request)
    {
        $data = $request->validated();
        $data['service_type'] = 'dine_in';
        /** @var TableSession $session */
        $session = $request->attributes->get('table_session');
        $order = $this->orders->create($data, $session->table, $session);

        return ApiResponse::created(CustomerOrderResource::make($order->load(self::WITH)), 'Pesanan diterima');
    }

    public function show(Request $request, string $publicToken)
    {
        return ApiResponse::success(CustomerOrderResource::make($this->findInSession($request, $publicToken)));
    }

    public function cancel(CancelOrderItemRequest $request, string $publicToken)
    {
        $data=$request->validated();
        $order = $this->orders->cancelItemByCustomer($this->findInSession($request, $publicToken),$data['detail_id'],$data['qty'],$data['reason'],$request->header('Idempotency-Key'));

        return ApiResponse::success(CustomerOrderResource::make($order->load(self::WITH)), 'Pesanan dibatalkan');
    }

    public function uploadProof(UploadPaymentProofRequest $request, string $publicToken, PaymentProofService $proofs)
    {
        $proof = $proofs->upload($this->findInSession($request, $publicToken), $request->file('proof'));
        return ApiResponse::created([
            'status' => $proof->status,
            'uploaded_at' => $proof->uploaded_at->toIso8601String(),
            'mime_type' => $proof->mime_type,
            'size_bytes' => $proof->size_bytes,
        ], 'Bukti pembayaran diterima dan menunggu verifikasi');
    }

    private function findInSession(Request $request, string $publicToken): Order
    {
        $session = $request->attributes->get('table_session');
        return Order::query()->with(self::WITH)->where('table_session_id', $session->id)->where('public_token', $publicToken)->firstOrFail();
    }
}
