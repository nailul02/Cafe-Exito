<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            throw new AuthenticationException;
        }
        if (! $user->is_active) {
            $user->currentAccessToken()?->delete();
            throw new AuthorizationException('Akun dinonaktifkan.');
        }

        return $next($request);
    }
}
