<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** Memaksa JSON, menetapkan X-Request-Id, dan mengatur Cache-Control (no-store untuk data operasional). */
class ForceJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        $incoming = (string) $request->header('X-Request-Id', '');
        $id = preg_match('/^[A-Za-z0-9._-]{8,64}$/', $incoming) ? $incoming : (string) Str::uuid();
        $request->attributes->set('request_id', $id);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $id);
        // Katalog menu publik boleh di-cache singkat; selainnya jangan (polling harus selalu segar).
        $cacheable = $request->isMethod('GET') && $request->is('api/v1/customer/menus');
        $response->headers->set('Cache-Control', $cacheable ? 'public, max-age=10' : 'no-store');

        return $response;
    }
}
