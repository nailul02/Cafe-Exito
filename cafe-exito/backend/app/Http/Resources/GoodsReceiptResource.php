<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wajib eager-load: details.purchaseDetail.ingredient, confirmer. @mixin \App\Models\GoodsReceipt */
class GoodsReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'purchase_id' => $this->purchase_id,
            'status' => Rp::enum($this->status),
            'received_at' => $this->received_at?->toDateString(),
            'note' => $this->note,
            'details' => $this->details->map(fn ($d) => [
                'purchase_detail_id' => $d->purchase_detail_id,
                'ingredient' => ['id' => $d->purchaseDetail->ingredient->id, 'name' => $d->purchaseDetail->ingredient->name, 'unit' => $d->purchaseDetail->ingredient->unit],
                'qty_received' => Rp::qty($d->qty_received),
            ])->values()->all(),
            'confirmed_by' => $this->confirmer?->name,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
        ];
    }
}
