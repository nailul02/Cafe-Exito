<?php

namespace App\Http\Middleware;

use App\Exceptions\DomainError;
use App\Models\Table;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Legacy QR-header validation for routes that still accept table QR context. */
class ValidateTableToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Table-Token');
        if ($token !== null && $token !== '') {
            $table = Table::query()->numbered()->where('qr_token', $token)->where('status', 'active')->first();
            if (! $table) {
                throw DomainError::tableInvalid('QR meja tidak dikenali atau sudah tidak berlaku.');
            }
            $request->attributes->set('table', $table);
        }

        return $next($request);
    }
}
