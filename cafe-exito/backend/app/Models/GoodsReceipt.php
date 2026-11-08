<?php

namespace App\Models;

use App\Enums\ReceiptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    protected $fillable = ['receipt_number', 'purchase_id', 'status', 'received_at', 'note', 'created_by', 'confirmed_by', 'confirmed_at'];

    protected function casts(): array
    {
        return ['status' => ReceiptStatus::class, 'received_at' => 'date', 'confirmed_at' => 'datetime'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class)->orderBy('id');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
