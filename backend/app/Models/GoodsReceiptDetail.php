<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDetail extends Model
{
    protected $fillable = ['goods_receipt_id', 'purchase_detail_id', 'qty_received'];

    protected function casts(): array
    {
        return ['qty_received' => 'decimal:3'];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function purchaseDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseDetail::class);
    }
}
