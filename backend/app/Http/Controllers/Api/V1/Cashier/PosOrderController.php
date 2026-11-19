<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePosOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\PosOrderService;
use App\Support\ApiResponse;

class PosOrderController extends Controller
{
    public function store(CreatePosOrderRequest $request, PosOrderService $orders)
    {
        $order = $orders->create($request->validated(), $request->user());

        return ApiResponse::created(OrderResource::make($order), 'Pesanan POS dibuat. Pembayaran masih menunggu konfirmasi kasir.');
    }
}
