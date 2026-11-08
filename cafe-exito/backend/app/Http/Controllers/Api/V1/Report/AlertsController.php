<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTicket;
use App\Services\DashboardService;
use App\Support\ApiResponse;

/** Penghitung ringan untuk ikon notifikasi (polling). Isi notifikasi final menunggu Figma/klarifikasi. */
class AlertsController extends Controller
{
    public function __invoke(DashboardService $dashboard)
    {
        return ApiResponse::success([
            'low_stock_count' => $dashboard->lowStock()->count(),
            'awaiting_payment' => Order::query()->where('status', OrderStatus::MenungguPembayaran->value)->count(),
            'new_tickets' => OrderTicket::query()->where('status', TicketStatus::Antre->value)->count(),
        ]);
    }
}
