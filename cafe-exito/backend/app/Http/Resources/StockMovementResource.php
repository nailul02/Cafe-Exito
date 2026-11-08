<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wajib eager-load: ingredient, user, receiptDetail.receipt.purchase. @mixin \App\Models\StockMovement */
class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reference = null;
        if ($this->order_ticket_id) {
            $reference = 'Tiket #'.$this->order_ticket_id;
        } elseif ($this->receiptDetail) {
            $receipt = $this->receiptDetail->receipt;
            $reference = $receipt->purchase->purchase_number.' / '.$receipt->receipt_number;
        }

        return [
            'id' => $this->id,
            'ingredient' => ['id' => $this->ingredient->id, 'name' => $this->ingredient->name, 'unit' => $this->ingredient->unit],
            'type' => Rp::enum($this->type),
            'quantity_change' => Rp::qty($this->quantity_change),
            'stock_after' => Rp::qty($this->stock_after),
            'user' => $this->user?->name,
            'reference' => $reference,
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
