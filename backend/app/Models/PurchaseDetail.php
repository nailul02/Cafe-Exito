<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    protected $fillable = ['purchase_id', 'ingredient_id', 'qty_ordered', 'qty_received', 'unit_price'];

    protected function casts(): array
    {
        return ['qty_ordered' => 'decimal:3', 'qty_received' => 'decimal:3', 'unit_price' => 'integer'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
    }

    public function outstanding(): float
    {
        return (float) $this->qty_ordered - (float) $this->qty_received;
    }
}
