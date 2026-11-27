<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Idempotent final catalog seeder. Old items are soft-deleted so order history remains intact. */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Milkshake' => [['Choco Classic', 12000], ['Vanila', 12000], ['Taro', 12000], ['Green Tea', 12000], ['Tiramisu', 12000], ['Bubblegum', 12000], ['Sweetberry', 13000]],
            'Coffee' => [['Espresso', 12000], ['Americano', 12000], ['Cafe Latte', 12000], ['Cappuchino', 12000], ['Vanila Latte', 12000], ['Caramel Latte', 12000], ['Mochacino', 12000]],
            'Tea' => [['Lychee Tea', 12000], ['Lemon Tea', 12000], ['Strawberry Tea', 12000]],
            'Tambahan Minuman' => [['Mineral Water', 5000], ['Es Kosong', 2000]],
            'Signature Drink' => [['Kopsus Exito', 12000], ['Kopsus Tiramisu', 13000], ['Caramel Machiato', 12000], ['Butterscout', 13000], ['Kopsus Regal', 13000], ['Triple Peach', 13000]],
            'Nasi Putih' => [['Nasi Telur Dadar', 10000], ['Nasi Telur Tahu Tempe', 12000], ['Nasi Ajaib Exito', 15000], ['Pecel Ayam', 13000]],
            'Olahan' => [['Nasi Goreng Biasa', 12000], ['Nasi Goreng Sosis', 15000], ['Nasi Goreng Nugget', 15000], ['Nasi Goreng Spesial', 18000], ['Mie Goreng Biasa', 12000], ['Mie Goreng Sosis', 15000], ['Mie Goreng Nugget', 15000], ['Mie Goreng Spesial', 18000], ['Mie Nyemek', 12000]],
            'Cemilan' => [['French Fries', 10000], ['Nugget', 10000], ['Sosis', 10000], ['Sharing Snack', 18000]],
            'Tambahan Makanan' => [['Nasi', 3000], ['Cabe Terasi', 3000], ['Cabe Ijo', 3000]],
        ];
        $names = collect($catalog)->flatten(1)->pluck(0)->all();
        if (count($names) !== 45 || count(array_unique(array_map('mb_strtolower', $names))) !== 45) {
            throw new RuntimeException('Final catalog must contain exactly 45 unique menu names.');
        }

        DB::transaction(function () use ($catalog, $names): void {
            foreach ($catalog as $categoryName => $items) {
                $category = MenuCategory::query()->where('name', $categoryName)->firstOrFail();
                foreach ($items as [$name, $price]) {
                    $matches = Menu::withTrashed()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->lockForUpdate()->get();
                    if ($matches->count() > 1) throw new RuntimeException("Duplicate menu name already exists: {$name}");
                    $menu = $matches->first();
                    $available = $menu ? (bool) $menu->is_available : true;
                    if (! $menu) $menu = new Menu();
                    $menu->fill(['name' => $name, 'category_id' => $category->id, 'price' => $price, 'station' => $category->station->value, 'is_available' => $available]);
                    if ($menu->trashed()) $menu->restore();
                    $menu->save();
                }
            }

            // Previously identified legacy items are archived, never hard-deleted; order snapshots stay untouched.
            Menu::query()->whereNotIn('name', $names)->get()->each(fn (Menu $menu) => $menu->delete());
        });
    }
}
