<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Services\PurchaseService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private const WITH = ['supplier', 'details.ingredient', 'receipts.details.purchaseDetail.ingredient', 'receipts.confirmer'];

    public function __construct(private readonly PurchaseService $purchases) {}

    public function index(Request $request)
    {
        $page = Purchase::query()->with(self::WITH)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('supplier_id'), fn ($q, $s) => $q->where('supplier_id', $s))
            ->orderByDesc('id')->paginate(min((int) $request->query('per_page', 25), 100));

        return ApiResponse::paginated($page, PurchaseResource::class);
    }

    public function show(Purchase $purchase)
    {
        return ApiResponse::success(PurchaseResource::make($purchase->load(self::WITH)));
    }

    public function store(SavePurchaseRequest $request)
    {
        $purchase = $this->purchases->create($request->validated(), $request->user());

        return ApiResponse::created(PurchaseResource::make($purchase->load(self::WITH)), 'Draft pembelian dibuat');
    }

    public function update(SavePurchaseRequest $request, Purchase $purchase)
    {
        $purchase = $this->purchases->update($purchase, $request->validated(), $request->user());

        return ApiResponse::success(PurchaseResource::make($purchase->load(self::WITH)), 'Draft pembelian diperbarui');
    }

    public function place(Request $request, Purchase $purchase)
    {
        $purchase = $this->purchases->place($purchase, $request->user());

        return ApiResponse::success(PurchaseResource::make($purchase->load(self::WITH)), 'Pembelian ditandai dipesan');
    }

    public function cancel(Request $request, Purchase $purchase)
    {
        $purchase = $this->purchases->cancel($purchase, $request->user());

        return ApiResponse::success(PurchaseResource::make($purchase->load(self::WITH)), 'Pembelian dibatalkan');
    }
}
