<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderItemRequest;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($order, $orders, $request) {
            foreach ($order->details as $detail) {
                $remaining = $detail->qty - (int) $detail->cancellations()->sum('qty');
                if ($remaining > 0) {
                    $orders->cancelItemByOwner($order, $detail->id, $remaining, $request->validated('reason'), $request->user());
                }
            }
        });

        return ApiResponse::success(OrderResource::make($order->load(self::WITH)), 'Pesanan dibatalkan');
    }

    public function cancelItem(CancelOrderItemRequest $request, Order $order, OrderService $orders)
    {
        $data = $request->validated();
        $order = $orders->cancelItemByOwner($order, $data['detail_id'], $data['qty'], $data['reason'], $request->user(), $request->header('Idempotency-Key'));

        return ApiResponse::success(OrderResource::make($order->load(self::WITH)), 'Item pesanan dibatalkan');
    }
}
