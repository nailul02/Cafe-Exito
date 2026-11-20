<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Membuat pesanan POS; pembayaran tetap tertunda sampai kasir mencatat pelunasan. */
class PosOrderService
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
        private readonly AuditService $audit,
    ) {}

    public function create(array $data, User $cashier): Order
    {
        return DB::transaction(function () use ($data, $cashier) {
            $existing = Order::query()
                ->where('client_request_id', $data['client_request_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing->load(['table', 'details', 'payment.verifier', 'tickets']);
            }

            $data['order_source'] = 'pos_cashier';
            $order = $this->orders->create($data);
            $order->update(['created_by'=>$cashier->id]);
            $this->audit->record($cashier, 'order.pos_created', 'order', $order->id, [
                'service_type' => $data['service_type'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
            ]);

            return $order->refresh()->load(['table', 'details', 'payment.verifier', 'tickets']);
        });
    }
}
