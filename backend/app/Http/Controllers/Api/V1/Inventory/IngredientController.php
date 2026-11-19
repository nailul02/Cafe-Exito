<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveIngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Services\AuditService;
use App\Services\StockService;
use App\Support\ApiResponse;
use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $rows = Ingredient::query()->where('is_active', true)
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($search !== '', fn ($q) => $q->where('name', 'ilike', '%'.addcslashes($search, '%_\\').'%'))
            ->orderBy('name')->get()
            // Level stok dihitung di PHP (aturan tunggal StockLevel), lalu disaring.
            ->filter(fn (Ingredient $i) => ! $status || ($status === 'low' ? $i->level()->isLow() : $i->level()->value === $status))
            ->values();

        return ApiResponse::success(IngredientResource::collection($rows));
    }

    /** Stok awal dicatat sebagai koreksi agar jejak pergerakan stok lengkap. */
    public function store(SaveIngredientRequest $request, StockService $stock)
    {
        $ingredient = DB::transaction(function () use ($request, $stock) {
            $data = $request->validated();
            $initial = (float) ($data['stock'] ?? 0);
            unset($data['stock']);
            $ingredient = Ingredient::create($data + ['stock' => 0, 'category' => $data['category'] ?? 'Bahan Makanan']);
            if ($initial > 0) {
                $stock->adjust($ingredient, $initial, $request->user(), 'Stok awal');
            }

            return $ingredient->refresh();
        });

        return ApiResponse::created(IngredientResource::make($ingredient), 'Bahan dibuat');
    }

    public function update(SaveIngredientRequest $request, Ingredient $ingredient, AuditService $audit)
    {
        $ingredient->update($request->validated());
        $audit->record($request->user(), 'ingredient.updated', 'ingredient', $ingredient->id, $request->validated());

        return ApiResponse::success(IngredientResource::make($ingredient->refresh()), 'Bahan diperbarui');
    }

    /** Menonaktifkan (soft). Bahan yang masih dipakai resep tetap tercatat untuk riwayat. */
    public function destroy(Request $request, Ingredient $ingredient, AuditService $audit)
    {
        $ingredient->update(['is_active' => false]);
        $audit->record($request->user(), 'ingredient.deactivated', 'ingredient', $ingredient->id);

        return ApiResponse::success(null, 'Bahan dinonaktifkan');
    }

    /** Ringkasan kartu Kelola Stok (Figma MG-03). */
    public function summary()
    {
        $active = Ingredient::query()->where('is_active', true)->get();
        $incoming = StockMovement::query()->join('ingredients', 'ingredients.id', '=', 'stock_movements.ingredient_id')
            ->where('stock_movements.type', StockMovementType::PenerimaanPembelian->value)
            ->where('stock_movements.created_at', '>=', now()->startOfDay())
            ->selectRaw('ingredients.unit AS unit, SUM(stock_movements.quantity_change) AS total')->groupBy('ingredients.unit')->get()
            ->map(fn ($r) => ['unit' => $r->unit, 'total' => Rp::qty($r->total)])->all();

        return ApiResponse::success([
            'total_items' => $active->count(),
            'critical_count' => $active->filter(fn ($i) => in_array($i->level()->value, ['kritis', 'habis'], true))->count(),
            'low_count' => $active->filter(fn ($i) => $i->level()->isLow())->count(),
            'incoming_today' => $incoming,
            'categories' => $active->groupBy('category')->map(fn ($g, $name) => ['name' => $name, 'count' => $g->count()])->values()->all(),
        ]);
    }
}
