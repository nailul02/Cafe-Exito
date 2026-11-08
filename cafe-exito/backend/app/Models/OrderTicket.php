<?php

namespace App\Models;

use App\Enums\Station;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTicket extends Model
{
    protected $fillable = ['order_id', 'station', 'status', 'started_at', 'ready_at', 'cancelled_at', 'handled_by'];

    protected function casts(): array
    {
        return [
            'station' => Station::class,
            'status' => TicketStatus::class,
            'started_at' => 'datetime',
            'ready_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
