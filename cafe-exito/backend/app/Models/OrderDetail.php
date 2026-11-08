<?php

namespace App\Models;

use App\Enums\Station;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = ['order_id', 'menu_id', 'menu_name', 'qty', 'unit_price', 'subtotal', 'station'];

    protected function casts(): array
    {
        return ['qty' => 'integer', 'unit_price' => 'integer', 'subtotal' => 'integer', 'station' => Station::class];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class)->withTrashed();
    }
}
