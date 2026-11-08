<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRecipeRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Services\AuditService;
use App\Services\MenuAvailabilityService;
use App\Support\ApiResponse;
use App\Support\Rp;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function show(Menu $menu)
    {
        $rows = $menu->recipes()->with('ingredient')->get()->map(fn ($r) => [
            'ingredient_id' => $r->ingredient_id, 'name' => $r->ingredient->name, 'unit' => $r->ingredient->unit,
            'quantity' => Rp::qty($r->quantity), 'level' => Rp::enum($r->ingredient->level()),
        ])->values()->all();

        return ApiResponse::success($rows);
    }

    /** Mengganti seluruh resep menu (BOM). */
    public function update(SaveRecipeRequest $request, Menu $menu, MenuAvailabilityService $availability, AuditService $audit)
    {
        DB::transaction(function () use ($request, $menu) {
            $menu->recipes()->delete();
            $menu->recipes()->createMany(array_map(
                fn ($i) => ['ingredient_id' => $i['ingredient_id'], 'quantity' => $i['quantity']],
                $request->validated('items'),
            ));
        });
        $audit->record($request->user(), 'menu.recipe_changed', 'menu', $menu->id, ['items' => count($request->validated('items'))]);

        return ApiResponse::success(MenuResource::make($availability->load($menu->newCollection([$menu->refresh()]))->first()), 'Resep disimpan');
    }
}
