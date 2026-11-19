<?php

namespace App\Http\Middleware;

use App\Exceptions\DomainError;
use App\Models\Table;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header X-Table-Token bersifat OPSIONAL (K-T2 PENDING: QR per meja vs satu QR umum).
 * Bila dikirim, token harus valid dan meja aktif; meja dipakai untuk mencocokkan nomor meja pesanan.
 */
class ValidateTableToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Table-Token');
        if ($token !== null && $token !== '') {
            $table = Table::query()->where('qr_token', $token)->where('status', 'active')->first();
            if (! $table) {
                throw DomainError::tableInvalid('QR meja tidak dikenali atau sudah tidak berlaku.');
            }
            $request->attributes->set('table', $table);
        }

        return $next($request);
    }
}
