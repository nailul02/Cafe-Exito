<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wajib eager-load: table, details, payment.verifier, tickets. @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_token' => $this->public_token,
            'order_number' => $this->order_number,
            'display_number' => $this->displayNumber(),
            'table' => $this->table ? ['id' => $this->table->id, 'number' => $this->table->table_number] : null,
            'service_type' => $this->service_type ?? 'dine_in',
            'order_source' => $this->order_source ?? 'qr_customer',
            'customer_name' => $this->customer_name,
            'status' => Rp::enum($this->status),
            'ordered_at' => $this->ordered_at->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'total' => $this->total,
            'items' => $this->details->map(fn ($d) => [
                'id' => $d->id, 'menu_id' => $d->menu_id, 'name' => $d->menu_name, 'qty' => $d->qty,
                'unit_price' => $d->unit_price, 'subtotal' => $d->subtotal, 'station' => $d->station->value,
            ])->values()->all(),
            'payment' => $this->payment ? PaymentResource::make($this->payment)->resolve($request) : null,
            'payment_history' => $this->relationLoaded('payments') ? $this->payments->flatMap(fn ($payment) => $payment->proofs->map(fn ($proof) => ['status' => $proof->status, 'reject_reason' => $proof->reject_reason, 'uploaded_at' => $proof->uploaded_at?->toIso8601String()]))->values()->all() : [],
            'tickets' => $this->tickets->map(fn ($t) => [
                'id' => $t->id, 'station' => $t->station->value, 'status' => Rp::enum($t->status),
                'started_at' => $t->started_at?->toIso8601String(), 'ready_at' => $t->ready_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
