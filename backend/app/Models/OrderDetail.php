<?php

namespace App\Models;

use App\Enums\Station;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = ['order_id', 'menu_id', 'menu_name', 'qty', 'unit_price', 'subtotal', 'station', 'item_type', 'started_at', 'ready_at'];

    protected function casts(): array
    {
        return ['qty' => 'integer', 'unit_price' => 'integer', 'subtotal' => 'integer', 'station' => Station::class, 'started_at' => 'datetime', 'ready_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class)->withTrashed();
    }

    public function cancellations() { return $this->hasMany(OrderItemCancellation::class); }

    public function activeQuantity(): int
    {
        return max(0, $this->qty - (int) $this->cancellations()->sum('qty'));
    }
}
