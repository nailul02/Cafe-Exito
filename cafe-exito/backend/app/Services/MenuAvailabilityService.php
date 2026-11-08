<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;

/**
 * Ketersediaan EFEKTIF menu = saklar manual (is_available) DAN stok setiap bahan resep cukup minimal satu porsi.
 * Dihitung dinamis (K-T4 ASUMSI: tidak disimpan), sehingga tidak ada status basi saat stok berubah.
 */
class MenuAvailabilityService
{
    /** @param Collection<int, Menu> $menus */
    public function load(Collection $menus): Collection
    {
        return $menus->load(['recipes.ingredient', 'category']);
    }

    /** @return array{orderable: bool, reason: ?string, max_portions: ?int} */
    public function evaluate(Menu $menu): array
    {
        if (! $menu->is_available) {
            return ['orderable' => false, 'reason' => 'Sedang tidak tersedia', 'max_portions' => null];
        }
        $max = null;
        foreach ($menu->recipes as $recipe) {
            $ingredient = $recipe->ingredient;
            if (! $ingredient || ! $ingredient->is_active) {
                return ['orderable' => false, 'reason' => 'Bahan tidak aktif', 'max_portions' => 0];
            }
            $portions = (int) floor((float) $ingredient->stock / max((float) $recipe->quantity, 0.001));
            if ($portions < 1) {
                return ['orderable' => false, 'reason' => 'Bahan habis', 'max_portions' => 0];
            }
            $max = $max === null ? $portions : min($max, $portions);
        }

        return ['orderable' => true, 'reason' => null, 'max_portions' => $max];
    }
}
