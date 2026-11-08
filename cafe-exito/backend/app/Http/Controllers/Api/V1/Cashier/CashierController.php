<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\DashboardService;
use App\Services\SettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    private const WITH = ['table', 'details', 'payment.verifier', 'tickets'];

    public function dashboard(DashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->cashier());
    }

    /** Daftar pesanan hari ini (Figma KS-01/KS-02) dengan filter status dan pencarian nomor. */
    public function orders(Request $request)
    {
        $status = data_get($request->query(), 'filter.status') ?? $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $orders = Order::query()->with(self::WITH)
            ->where('ordered_at', '>=', now()->startOfDay())
            ->when($status && $status !== 'semua', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('order_number', 'ilike', '%'.addcslashes($search, '%_\\').'%'))
            ->orderByDesc('id')->limit(200)->get();

        return ApiResponse::success(OrderResource::collection($orders));
    }

    public function order(Order $order)
    {
        return ApiResponse::success(OrderResource::make($order->load(self::WITH)));
    }

    /** Data struk (Figma KS-03). Pencetakan dilakukan frontend/perangkat kasir. */
    public function receipt(Order $order, SettingsService $settings)
    {
        $order->load(self::WITH);
        /** @var Payment|null $payment */
        $payment = $order->payment;

        return ApiResponse::success([
            'cafe' => [
                'name' => $settings->get('cafe_name', 'Cafe Exito'),
                'address' => $settings->get('cafe_address'),
                'footer' => $settings->get('receipt_footer'),
            ],
            'transaction_number' => $payment?->transaction_number,
            'order' => OrderResource::make($order)->resolve(request()),
            'cashier' => $payment?->verifier?->name,
        ]);
    }
}
