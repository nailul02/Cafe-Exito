<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat status (append-only). */
class OrderStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['order_id', 'order_ticket_id', 'station', 'from_status', 'to_status', 'user_id', 'note'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
