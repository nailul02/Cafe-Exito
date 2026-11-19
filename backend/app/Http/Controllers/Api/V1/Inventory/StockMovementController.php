<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Riwayat pergerakan stok (hanya baca; tabel append-only). */
class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $page = StockMovement::query()->with(['ingredient', 'user', 'receiptDetail.receipt.purchase'])
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->query('ingredient_id'), fn ($q, $i) => $q->where('ingredient_id', $i))
            ->orderByDesc('id')->paginate(min((int) $request->query('per_page', 30), 100));

        return ApiResponse::paginated($page, StockMovementResource::class);
    }
}
