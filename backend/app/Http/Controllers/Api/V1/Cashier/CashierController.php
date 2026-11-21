<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Services\DashboardService;
use App\Services\SettingsService;
use App\Services\OrderStatusService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    private const WITH = ['table', 'details.cancellations.refundItem.refundRequest', 'payment.verifier', 'payment.latestProof', 'payments.proofs', 'tickets'];

    public function dashboard(DashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->cashier());
    }

    /** Meja aktif yang dapat dipilih pada POS kasir. */
    public function tables()
    {
        $tables = Table::query()->where('status', 'active')->orderBy('table_number')
            ->get(['id', 'table_number'])
            ->map(fn (Table $table) => ['id' => $table->id, 'table_number' => $table->table_number]);

        return ApiResponse::success($tables);
    }

    /** Daftar pesanan hari ini (Figma KS-01/KS-02) dengan filter status dan pencarian nomor. */
    public function orders(Request $request)
    {
        $status = data_get($request->query(), 'filter.status') ?? $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $orders = Order::query()->with(self::WITH)
            ->where('ordered_at', '>=', now()->startOfDay())
            ->when($status === 'menunggu_pembayaran', fn ($q) => $q->whereHas('payment', fn ($payment) => $payment->where('status', 'pending')))
            ->when($status && ! in_array($status, ['semua', 'menunggu_pembayaran'], true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('order_number', 'ilike', '%'.addcslashes($search, '%_\\').'%'))
            ->orderByDesc('id')->limit(200)->get();

        return ApiResponse::success(OrderResource::collection($orders));
    }

    public function order(Order $order)
    {
        return ApiResponse::success(OrderResource::make($order->load(self::WITH)));
    }

    public function completeOrder(Order $order, Request $request, OrderStatusService $statuses)
    {
        return ApiResponse::success(OrderResource::make($statuses->complete($order, $request->user())->load(self::WITH)), 'Pesanan ditandai selesai');
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
