<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;

/** Ketersediaan menu hanya mengikuti saklar operasional manual; stok tidak lagi digunakan. */
class MenuAvailabilityService
{
    /** @param Collection<int, Menu> $menus */
    public function load(Collection $menus): Collection
    {
        return $menus->load('category');
    }

    /** @return array{orderable: bool, reason: ?string} */
    public function evaluate(Menu $menu): array
    {
        if (! $menu->is_available) {
            return ['orderable' => false, 'reason' => 'Sedang tidak tersedia'];
        }
        return ['orderable' => true, 'reason' => null];
    }
}
