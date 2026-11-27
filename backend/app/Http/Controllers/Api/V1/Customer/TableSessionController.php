<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenTableSessionRequest;
use App\Services\TableSessionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class TableSessionController extends Controller
{
    public function open(OpenTableSessionRequest $request, TableSessionService $sessions)
    {
        return ApiResponse::success($sessions->open($request->validated('qr_token'), $request->validated('customer_name')));
    }

    public function current(Request $request, TableSessionService $sessions)
    {
        return ApiResponse::success($sessions->snapshot($request->attributes->get('table_session')));
    }
}
