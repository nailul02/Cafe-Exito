<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'method' => Rp::enum($this->method),
            'status' => Rp::enum($this->status),
            'amount' => $this->amount,
            'amount_received' => $this->amount_received,
            'change' => $this->amount_received !== null ? $this->amount_received - $this->amount : null,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'verified_by' => $this->verifier?->name,
            'transaction_number' => $this->transaction_number,
        ];
    }
}
