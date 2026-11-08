<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use App\Services\AuditService;
use App\Services\StockService;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\DB;

/** Koreksi / stock opname. Catatan wajib; hasil stok tidak boleh negatif. */
class StockAdjustmentController extends Controller
{
    public function store(StockAdjustmentRequest $request, StockService $stock, AuditService $audit)
    {
        $data = $request->validated();
        $ingredient = DB::transaction(function () use ($data, $request, $stock, $audit) {
            $row = $stock->adjust(Ingredient::query()->findOrFail($data['ingredient_id']), (float) $data['quantity_change'], $request->user(), $data['note']);
            $audit->record($request->user(), 'stock.adjusted', 'ingredient', $row->id, ['change' => $data['quantity_change'], 'note' => $data['note']]);

            return $row;
        });

        return ApiResponse::success(IngredientResource::make($ingredient), 'Stok dikoreksi');
    }
}
