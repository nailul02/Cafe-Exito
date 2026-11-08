<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\DomainError;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Support\NumberGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Pembayaran = tunai atau QRIS statis dengan VERIFIKASI MANUAL kasir. Tidak ada payment gateway.
 * Antarmuka gateway sengaja belum dibuat (tidak diperlukan oleh keputusan proyek).
 */
class PaymentService
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly OrderService $orders,
        private readonly StatusLogger $log,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  ?string  $method  koreksi metode oleh kasir (opsional; K-T9 ASUMSI, tercatat di audit)
     * @param  ?int  $amountReceived  uang diterima (tunai); bila kosong dianggap pas sebesar tagihan
     */
    public function verify(Payment $payment, User $cashier, ?string $method = null, ?int $amountReceived = null): Order
    {
        return DB::transaction(function () use ($payment, $cashier, $method, $amountReceived) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Pending) {
                throw DomainError::paymentAlreadyProcessed();
            }
            if (! $order->status->canTransitionTo(OrderStatus::Dibayar)) {
                throw DomainError::invalidTransition('Pesanan', $order->status->value, OrderStatus::Dibayar->value);
            }

            $changedMethod = null;
            if ($method !== null && $method !== $payment->method->value) {
                $changedMethod = ['from' => $payment->method->value, 'to' => $method];
                $payment->method = PaymentMethod::from($method);
            }
            if ($payment->method === PaymentMethod::Tunai) {
                if ($amountReceived !== null && $amountReceived < $payment->amount) {
                    throw DomainError::amountTooLow($payment->amount);
                }
                $payment->amount_received = $amountReceived ?? $payment->amount;
            } else {
                $payment->amount_received = null;
            }

            $payment->fill([
                'status' => PaymentStatus::Verified->value,
                'paid_at' => now(),
                'verified_by' => $cashier->id,
                'transaction_number' => NumberGenerator::next('TRX', 'payments', 'transaction_number'),
            ])->save();

            $from = $order->status->value;
            $order->update(['status' => OrderStatus::Dibayar->value, 'paid_at' => now()]);
            $this->log->order($order, $from, OrderStatus::Dibayar->value, $cashier, 'Pembayaran diverifikasi');
            $this->tickets->createForOrder($order, $cashier);
            $this->audit->record($cashier, 'payment.verified', 'payment', $payment->id, array_filter(['order_id' => $order->id, 'method_changed' => $changedMethod]));

            return $order;
        });
    }

    public function reject(Payment $payment, User $cashier, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($payment, $cashier, $reason) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            if ($payment->status !== PaymentStatus::Pending) {
                throw DomainError::paymentAlreadyProcessed();
            }
            $reason = $reason ?: 'Ditolak kasir'; // K-T9: alasan opsional karena Figma tidak memuat kolomnya
            $payment->update(['status' => PaymentStatus::Rejected->value, 'reject_reason' => $reason, 'verified_by' => $cashier->id]);
            $this->orders->markCancelled($order, $reason, $cashier);
            $this->audit->record($cashier, 'payment.rejected', 'payment', $payment->id, ['order_id' => $order->id, 'reason' => $reason]);

            return $order;
        });
    }
}
