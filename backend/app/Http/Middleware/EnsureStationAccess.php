<?php

namespace App\Http\Middleware;

use App\Enums\Station;
use App\Services\PermissionService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Pemisahan Barista/Kitchen: ->middleware('station:read') atau 'station:act'.
 * Stasiun yang bukan miliknya dijawab 404 (bukan 403) agar keberadaan data tidak bocor.
 */
class EnsureStationAccess
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string $mode = 'read'): Response
    {
        $station = Station::tryFrom((string) $request->route('station'));
        if (! $station) {
            throw new NotFoundHttpException;
        }
        $user = $request->user();
        $canAct = $this->permissions->has($user, $station->permission());
        $canRead = $canAct || $this->permissions->has($user, 'queue.view_all');

        if ($mode === 'act') {
            if (! $canAct) {
                throw $canRead ? new AuthorizationException : new NotFoundHttpException;
            }
        } elseif (! $canRead) {
            throw new NotFoundHttpException;
        }
        $request->attributes->set('station', $station);

        return $next($request);
    }
}
