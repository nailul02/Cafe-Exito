<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wajib eager-load: order.table, order.details, handler. @mixin \App\Models\OrderTicket */
class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $order = $this->order;

        return [
            'id' => $this->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'display_number' => $order->displayNumber(),
            'table_number' => $order->table->table_number,
            'station' => $this->station->value,
            'status' => Rp::enum($this->status),
            'ordered_at' => $order->ordered_at->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'ready_at' => $this->ready_at?->toIso8601String(),
            'handled_by' => $this->handler?->name,
            'order_status' => Rp::enum($order->status),
            // Hanya item milik stasiun ini (Barista tidak melihat makanan, dan sebaliknya).
            'items' => $order->details->where('station', $this->station)->map(fn ($d) => ['name' => $d->menu_name, 'qty' => $d->qty])->values()->all(),
        ];
    }
}
