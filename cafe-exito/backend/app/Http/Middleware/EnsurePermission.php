<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian: ->middleware('permission:payments.verify') atau 'permission:a,b' (salah satu cukup). */
class EnsurePermission
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string ...$any): Response
    {
        $user = $request->user();
        foreach ($any as $permission) {
            if ($user && $this->permissions->has($user, $permission)) {
                return $next($request);
            }
        }
        throw new AuthorizationException('Anda tidak memiliki izin untuk aksi ini.');
    }
}
