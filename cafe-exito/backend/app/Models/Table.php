<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = ['table_number', 'qr_token', 'status'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Tautan yang dicetak pada QR: mengarah ke halaman FRONTEND (bukan backend). Jalur final: K-T2 PENDING. */
    public function qrUrl(): string
    {
        $path = str_replace('{token}', $this->qr_token, (string) config('cafe.frontend_qr_path'));

        return config('cafe.frontend_url').'/'.ltrim($path, '/');
    }
}
