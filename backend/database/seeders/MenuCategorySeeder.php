<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Final menu category structure. Existing rows are renamed in place to preserve menu FKs. */
class MenuCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Minuman' => 'Coffee', 'Makanan' => 'Olahan'] as $old => $new) {
            $legacy = MenuCategory::query()->where('name', $old)->first();
            if ($legacy && ! MenuCategory::query()->where('name', $new)->whereKeyNot($legacy->id)->exists()) {
                $legacy->name = $new;
                $legacy->save();
            }
        }

        $rows = [
            ['Milkshake', 'minuman', 'barista', 1],
            ['Coffee', 'minuman', 'barista', 2],
            ['Tea', 'minuman', 'barista', 3],
            ['Tambahan Minuman', 'minuman', 'barista', 4],
            ['Signature Drink', 'minuman', 'barista', 5],
            ['Nasi Putih', 'makanan', 'kitchen', 6],
            ['Olahan', 'makanan', 'kitchen', 7],
            ['Cemilan', 'cemilan', 'kitchen', 8],
            ['Tambahan Makanan', 'makanan', 'kitchen', 9],
        ];

        foreach ($rows as [$name, $group, $station, $order]) {
            $attributes = ['display_group' => $group, 'station' => $station, 'sort_order' => $order];
            // The live legacy schema has required columns not present in canonical migrations.
            if (Schema::hasColumn('menu_categories', 'slug')) $attributes['slug'] = Str::slug($name);
            if (Schema::hasColumn('menu_categories', 'item_type_mapping')) {
                $attributes['item_type_mapping'] = $group === 'minuman' ? 'drink' : ($group === 'cemilan' ? 'snack' : 'food');
            }
            MenuCategory::query()->updateOrCreate(['name' => $name], $attributes);
        }
    }
}
