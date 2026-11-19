<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wajib eager-load: supplier, details.ingredient, receipts.details.purchaseDetail.ingredient. @mixin \App\Models\Purchase */
class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_number' => $this->purchase_number,
            'supplier' => ['id' => $this->supplier->id, 'name' => $this->supplier->name],
            'status' => Rp::enum($this->status),
            'ordered_at' => $this->ordered_at?->toIso8601String(),
            'note' => $this->note,
            'total' => (int) round($this->details->sum(fn ($d) => (float) $d->qty_ordered * $d->unit_price)),
            'details' => $this->details->map(fn ($d) => [
                'id' => $d->id,
                'ingredient' => ['id' => $d->ingredient->id, 'name' => $d->ingredient->name, 'unit' => $d->ingredient->unit],
                'qty_ordered' => Rp::qty($d->qty_ordered),
                'qty_received' => Rp::qty($d->qty_received),
                'qty_outstanding' => Rp::qty($d->outstanding()),
                'unit_price' => $d->unit_price,
            ])->values()->all(),
            'receipts' => GoodsReceiptResource::collection($this->receipts)->resolve($request),
        ];
    }
}
