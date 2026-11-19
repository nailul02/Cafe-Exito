<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = ['payment_id', 'path', 'mime_type', 'size_bytes', 'sha256', 'status', 'reject_reason', 'reviewed_by', 'uploaded_at', 'reviewed_at'];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer', 'uploaded_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
