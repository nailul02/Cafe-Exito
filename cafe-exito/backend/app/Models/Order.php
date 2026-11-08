<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'public_token', 'order_number', 'client_request_id', 'table_id', 'status', 'ordered_at', 'paid_at',
        'completed_at', 'cancelled_at', 'cancel_reason', 'cancelled_by', 'total',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'integer',
            'ordered_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(OrderTicket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Pembayaran terbaru (yang aktif/terakhir). */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /** Nomor tampil pendek (Figma: #010) dari nomor urut harian pada order_number. */
    public function displayNumber(): string
    {
        return '#'.substr($this->order_number, -3);
    }
}
