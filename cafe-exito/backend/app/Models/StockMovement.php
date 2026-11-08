<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Jejak pergerakan stok: append-only (tidak ada pembaruan atau penghapusan oleh aplikasi). */
class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['ingredient_id', 'type', 'quantity_change', 'stock_after', 'user_id', 'order_ticket_id', 'goods_receipt_detail_id', 'note'];

    protected function casts(): array
    {
        return ['type' => StockMovementType::class, 'quantity_change' => 'decimal:3', 'stock_after' => 'decimal:3'];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(OrderTicket::class, 'order_ticket_id');
    }

    public function receiptDetail(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptDetail::class, 'goods_receipt_detail_id');
    }
}
