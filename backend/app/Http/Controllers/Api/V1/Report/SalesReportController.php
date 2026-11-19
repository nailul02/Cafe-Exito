<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesReportRequest;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Illuminate\Support\Carbon;

class SalesReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function sales(SalesReportRequest $request)
    {
        $v = $request->validated();

        return ApiResponse::success($this->reports->sales($v['period'] ?? 'daily', $v['from'] ?? null, $v['to'] ?? null, $v['granularity'] ?? null));
    }

    public function stockUsage(SalesReportRequest $request)
    {
        $v = $request->validated();
        [$from, $to] = isset($v['from']) ? [Carbon::parse($v['from'])->startOfDay(), Carbon::parse($v['to'] ?? $v['from'])->endOfDay()] : [null, null];

        return ApiResponse::success($this->reports->stockUsage($from, $to));
    }
}
