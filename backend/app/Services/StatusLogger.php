<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\OrderTicket;
use App\Models\User;

/** Menulis riwayat perubahan status pesanan dan tiket (append-only). */
class StatusLogger
{
    public function order(Order $order, ?string $from, string $to, ?User $user = null, ?string $note = null): void
    {
        OrderStatusHistory::create(['order_id' => $order->id, 'from_status' => $from, 'to_status' => $to, 'user_id' => $user?->id, 'note' => $note]);
    }

    public function ticket(Order $order, OrderTicket $ticket, ?string $from, string $to, ?User $user = null, ?string $note = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id, 'order_ticket_id' => $ticket->id, 'station' => $ticket->station->value,
            'from_status' => $from, 'to_status' => $to, 'user_id' => $user?->id, 'note' => $note,
        ]);
    }
}
