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
use Illuminate\Validation\ValidationException;

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
        private readonly PaymentProofService $proofs,
    ) {}

    /**
     * @param  ?string  $method  koreksi metode oleh kasir (opsional; K-T9 ASUMSI, tercatat di audit)
     * @param  ?int  $amountReceived  uang diterima (tunai); bila kosong dianggap pas sebesar tagihan
     */
    public function verify(Payment $payment, User $cashier, ?string $method = null, ?int $amountReceived = null): Order
    {
        return DB::transaction(function () use ($payment, $cashier, $method, $amountReceived) {
            $orderId = Payment::query()->whereKey($payment->id)->value('order_id');
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Pending) {
                throw DomainError::paymentAlreadyProcessed();
            }
            if ($payment->method === PaymentMethod::Qris && $method === PaymentMethod::Tunai->value) {
                throw ValidationException::withMessages(['method' => 'Pembayaran QRIS tidak dapat diubah menjadi tunai saat verifikasi.']);
            }
            if ($order->order_source === 'qr_customer') {
                if ($method !== null && $method !== $payment->method->value) {
                    throw ValidationException::withMessages(['method' => 'Metode pembayaran Self-Order tidak dapat diubah saat verifikasi.']);
                }
                if ($payment->method === PaymentMethod::Qris && ! $payment->proofs()->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages(['payment' => 'Bukti QRIS pelanggan yang menunggu verifikasi wajib tersedia.']);
                }
            }
            $orderAlreadyCompleted = $order->status === OrderStatus::Selesai;
            if (! $orderAlreadyCompleted && ! $order->status->canTransitionTo(OrderStatus::Dibayar)) {
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
            if ($order->order_source === 'qr_customer' && $payment->proofs()->where('status', 'pending')->exists()) {
                $this->proofs->review($payment, $cashier, true);
            }

            $payment->fill([
                'status' => PaymentStatus::Verified->value,
                'paid_at' => now(),
                'verified_by' => $cashier->id,
                'transaction_number' => NumberGenerator::next('TRX', 'payments', 'transaction_number'),
            ])->save();

            if ($orderAlreadyCompleted) {
                // Completion is an operational state; recording payment later must not undo it.
                $order->update(['paid_at' => now()]);
            } else {
                $from = $order->status->value;
                $order->update(['status' => OrderStatus::Dibayar->value, 'paid_at' => now()]);
                $this->log->order($order, $from, OrderStatus::Dibayar->value, $cashier, 'Pembayaran diverifikasi');
                $this->tickets->createForOrder($order, $cashier);
            }
            $this->audit->record($cashier, 'payment.verified', 'payment', $payment->id, array_filter(['order_id' => $order->id, 'method_changed' => $changedMethod]));

            return $order;
        });
    }

    public function reject(Payment $payment, User $cashier, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($payment, $cashier, $reason) {
            $orderId = Payment::query()->whereKey($payment->id)->value('order_id');
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status !== PaymentStatus::Pending) {
                throw DomainError::paymentAlreadyProcessed();
            }
            if ($reason === null || trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'Alasan penolakan wajib diisi.']);
            }
            if ($payment->method === PaymentMethod::Tunai) {
                throw ValidationException::withMessages(['payment' => 'Pembayaran tunai tetap menunggu pelunasan; catat setelah uang diterima.']);
            }
            if ($order->order_source === 'qr_customer') {
                if ($payment->method !== PaymentMethod::Qris || ! $payment->proofs()->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages(['payment' => 'Bukti QRIS pelanggan yang menunggu verifikasi wajib tersedia.']);
                }
                $this->proofs->review($payment, $cashier, false, $reason);
            }
            $payment->update(['status' => PaymentStatus::Rejected->value, 'reject_reason' => $reason, 'verified_by' => $cashier->id]);
            if ($order->order_source !== 'qr_customer') {
                $this->orders->markCancelled($order, $reason, $cashier);
            }
            $this->audit->record($cashier, 'payment.rejected', 'payment', $payment->id, ['order_id' => $order->id, 'reason' => $reason]);

            return $order;
        });
    }
}
