<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveReceiptRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Services\GoodsReceiptService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Penerimaan barang. Respons selalu pembelian induk (lengkap dengan penerimaannya), sama seperti kontrak frontend. */
class GoodsReceiptController extends Controller
{
    private const WITH = ['supplier', 'details.ingredient', 'receipts.details.purchaseDetail.ingredient', 'receipts.confirmer'];

    public function __construct(private readonly GoodsReceiptService $receipts) {}

    public function store(SaveReceiptRequest $request, Purchase $purchase)
    {
        $this->receipts->createDraft($purchase, $request->validated('details'), $request->validated('note'), $request->user(), $request->validated('received_at'));

        return ApiResponse::created(PurchaseResource::make($purchase->refresh()->load(self::WITH)), 'Draft penerimaan dibuat');
    }

    public function update(SaveReceiptRequest $request, GoodsReceipt $receipt)
    {
        $this->receipts->updateDraft($receipt, $request->validated('details'), $request->validated('note'));

        return ApiResponse::success(PurchaseResource::make($receipt->purchase->refresh()->load(self::WITH)), 'Draft penerimaan diperbarui');
    }

    public function show(GoodsReceipt $receipt)
    {
        return ApiResponse::success(PurchaseResource::make($receipt->purchase->load(self::WITH)));
    }

    /** Menambah stok. Transaksi tunggal; penerimaan terkunci setelahnya. */
    public function confirm(Request $request, GoodsReceipt $receipt)
    {
        $this->receipts->confirm($receipt, $request->user());

        return ApiResponse::success(PurchaseResource::make($receipt->purchase->refresh()->load(self::WITH)), 'Barang diterima dan stok bertambah');
    }
}
