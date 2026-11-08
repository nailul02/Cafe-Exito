<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Monitoring pesanan untuk Owner/Manager (hanya baca) dan pembatalan oleh Manager. */
class OrderController extends Controller
{
    private const WITH = ['table', 'details', 'payment.verifier', 'tickets'];

    public function index(Request $request)
    {
        $status = data_get($request->query(), 'filter.status');
        $search = trim((string) $request->query('search', ''));
        $page = Order::query()->with(self::WITH)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('order_number', 'ilike', '%'.addcslashes($search, '%_\\').'%'))
            ->orderByDesc('id')->paginate(min((int) $request->query('per_page', 25), 100));

        return ApiResponse::paginated($page, OrderResource::class);
    }

    public function show(Order $order)
    {
        return ApiResponse::success(OrderResource::make($order->load([...self::WITH, 'histories.user'])));
    }

    public function cancel(CancelOrderRequest $request, Order $order, OrderService $orders)
    {
        $order = $orders->cancelByAdmin($order, $request->validated('reason'), (bool) $request->validated('restore_stock', false), $request->user());

        return ApiResponse::success(OrderResource::make($order->load(self::WITH)), 'Pesanan dibatalkan');
    }
}
