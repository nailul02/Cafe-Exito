<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Exceptions\DomainError;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Cashier-owned final order status transition after all production stations confirm readiness. */
class OrderStatusService
{
    public function __construct(private readonly StatusLogger $log, private readonly AuditService $audit) {}

    public function complete(Order $order, User $cashier): Order
    {
        return DB::transaction(function () use ($order, $cashier) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $tickets = $order->tickets()->lockForUpdate()->get();
            if ($tickets->isEmpty() || $tickets->contains(fn ($ticket) => $ticket->status !== TicketStatus::Selesai)) {
                throw DomainError::invalidTransition('Pesanan', $order->status->value, 'selesai_sebelum_semua_tiket_selesai');
            }
            if (! $order->status->canTransitionTo(OrderStatus::Selesai)) {
                throw DomainError::invalidTransition('Pesanan', $order->status->value, OrderStatus::Selesai->value);
            }

            $before = $order->status->value;
            $order->update(['status' => OrderStatus::Selesai->value, 'completed_at' => now()]);
            $this->log->order($order, $before, OrderStatus::Selesai->value, $cashier, 'Kasir mengonfirmasi seluruh tiket siap');
            $this->audit->record($cashier, 'order.completed_by_cashier', 'order', $order->id, ['before' => $before, 'after' => OrderStatus::Selesai->value]);

            return $order->fresh();
        });
    }
}
