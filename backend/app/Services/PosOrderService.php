<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Exceptions\DomainError;
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
                $expectedTableId = $data['service_type'] === 'dine_in' ? (int) ($data['table_id'] ?? 0) : null;
                if ($existing->service_type !== $data['service_type']
                    || (int) $existing->table_id !== (int) $expectedTableId) {
                    throw DomainError::tableInvalid('ID permintaan ini sudah digunakan untuk jenis layanan atau meja lain.');
                }
                return $existing->load(['table', 'details', 'payment.verifier', 'tickets']);
            }

            $data['order_source'] = 'pos_cashier';
            if ($data['service_type'] === 'dine_in') {
                $table = Table::query()->numbered()->whereKey($data['table_id'] ?? null)->where('status', 'active')->first();
                if (! $table) {
                    throw DomainError::tableInvalid('Pilih meja aktif dari daftar Meja 1–15.');
                }
                $data['table_number'] = $table->table_number;
            } else {
                $data['table_number'] = null;
                unset($data['table_id']);
            }
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
