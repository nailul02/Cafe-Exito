<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ['purchase_number', 'supplier_id', 'ordered_by', 'status', 'ordered_at', 'cancelled_at', 'note'];

    protected function casts(): array
    {
        return ['status' => PurchaseStatus::class, 'ordered_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function orderer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class)->orderBy('id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class)->orderBy('id');
    }
}
