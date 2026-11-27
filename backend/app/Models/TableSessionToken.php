<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableSessionToken extends Model
{
    protected $fillable = ['table_session_id', 'token_hash', 'revoked_at'];
    protected function casts(): array { return ['revoked_at' => 'datetime']; }
    public function session(): BelongsTo { return $this->belongsTo(TableSession::class, 'table_session_id'); }
}
