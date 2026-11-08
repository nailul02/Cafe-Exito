<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\CustomerOrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Pesanan diakses pelanggan HANYA lewat public_token (ULID acak), bukan ID berurut. */
class CustomerOrderController extends Controller
{
    private const WITH = ['table', 'details', 'payment', 'tickets'];

    public function __construct(private readonly OrderService $orders) {}

    public function store(CreateOrderRequest $request)
    {
        $order = $this->orders->create($request->validated(), $request->attributes->get('table'));

        return ApiResponse::created(CustomerOrderResource::make($order->load(self::WITH)), 'Pesanan diterima');
    }

    public function show(string $publicToken)
    {
        return ApiResponse::success(CustomerOrderResource::make($this->find($publicToken)));
    }

    public function cancel(Request $request, string $publicToken)
    {
        $order = $this->orders->cancelByCustomer($this->find($publicToken));

        return ApiResponse::success(CustomerOrderResource::make($order->load(self::WITH)), 'Pesanan dibatalkan');
    }

    private function find(string $publicToken): Order
    {
        return Order::query()->with(self::WITH)->where('public_token', $publicToken)->firstOrFail();
    }
}
