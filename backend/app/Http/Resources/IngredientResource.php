<?php

namespace App\Http\Resources;

use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Ingredient */
class IngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'unit' => $this->unit,
            'stock' => Rp::qty($this->stock),
            'min_stock' => Rp::qty($this->min_stock),
            'stock_percent' => $this->stockPercent(),
            'level' => Rp::enum($this->level()),
            'is_active' => $this->is_active,
        ];
    }
}
