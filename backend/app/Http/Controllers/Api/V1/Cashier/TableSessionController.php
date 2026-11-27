<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Models\TableSession;
use App\Services\TableSessionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class TableSessionController extends Controller
{
    public function index(TableSessionService $sessions)
    {
        $active = TableSession::query()->with('table')->where('status', 'active')->orderBy('table_id')->get();
        return ApiResponse::success($active->map(fn ($session) => ['id' => $session->id] + $sessions->snapshot($session))->values());
    }

    public function show(TableSession $tableSession, TableSessionService $sessions)
    {
        return ApiResponse::success(['id' => $tableSession->id] + $sessions->snapshot($tableSession));
    }

    public function close(Request $request, TableSession $tableSession, TableSessionService $sessions)
    {
        $closed = $sessions->close($tableSession, $request->user());
        return ApiResponse::success(['id' => $closed->id] + $sessions->snapshot($closed), 'Sesi meja ditutup');
    }
}
