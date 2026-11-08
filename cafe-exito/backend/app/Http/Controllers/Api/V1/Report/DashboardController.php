<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Support\ApiResponse;

class DashboardController extends Controller
{
    public function owner(DashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->owner());
    }

    public function manager(DashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->manager());
    }
}
