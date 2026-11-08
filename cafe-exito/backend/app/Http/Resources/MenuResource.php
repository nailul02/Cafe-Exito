<?php

namespace App\Http\Resources;

use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Menu untuk staf. Wajib eager-load: category, recipes.ingredient.
 *
 * @mixin \App\Models\Menu
 */
class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $state = app(MenuAvailabilityService::class)->evaluate($this->resource);

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'category' => ['id' => $this->category->id, 'name' => $this->category->name],
            'display_group' => $this->category->display_group->value,
            'station' => $this->station->value,
            'is_available' => $this->is_available,
            'is_orderable' => $state['orderable'],
            'unavailable_reason' => $state['reason'],
            'has_recipe' => $this->recipes->isNotEmpty(),
            'max_portions' => $state['max_portions'],
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
        ];
    }
}
