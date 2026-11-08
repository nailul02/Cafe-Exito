<?php

namespace App\Http\Resources;

use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** Menu untuk pelanggan: tanpa SKU, HPP, stok, atau jumlah porsi. @mixin \App\Models\Menu */
class CustomerMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $state = app(MenuAvailabilityService::class)->evaluate($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'category' => ['id' => $this->category->id, 'name' => $this->category->name],
            'display_group' => $this->category->display_group->value,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'is_orderable' => $state['orderable'],
            'unavailable_reason' => $state['reason'],
        ];
    }
}
