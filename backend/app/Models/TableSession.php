<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSession extends Model
{
    protected $fillable = ['table_id', 'customer_name', 'status', 'opened_at', 'closed_at', 'closed_by', 'closing_total', 'closing_paid', 'closing_balance'];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime', 'closed_at' => 'datetime', 'closing_total' => 'integer', 'closing_paid' => 'integer', 'closing_balance' => 'integer'];
    }

    public function table(): BelongsTo { return $this->belongsTo(Table::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function tokens(): HasMany { return $this->hasMany(TableSessionToken::class); }
}
