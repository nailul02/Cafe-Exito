<?php

namespace App\Http\Middleware;

use App\Exceptions\DomainError;
use App\Models\TableSessionToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTableSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->header('X-Table-Session', '');
        if ($token === '') throw new DomainError('TABLE_SESSION_REQUIRED', 'Scan QR meja untuk membuka sesi pemesanan.', 401);

        $credential = TableSessionToken::query()->with('session.table')
            ->where('token_hash', hash('sha256', $token))->whereNull('revoked_at')->first();
        if (! $credential || $credential->session->status !== 'active' || $credential->session->table->status !== 'active') {
            throw new DomainError('TABLE_SESSION_INVALID', 'Sesi meja tidak aktif. Silakan scan ulang QR meja.', 401);
        }
        $request->attributes->set('table_session', $credential->session);
        return $next($request);
    }
}
