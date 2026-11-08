<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

/** Kategori contoh dari Figma. Default station mengikuti kategori (ASUMSI: cocokkan dengan menu nyata Cafe Exito). */
class MenuCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Signature Espresso', 'minuman', 'barista', 1],
            ['Manual Brew', 'minuman', 'barista', 2],
            ['Non-Coffee & Tea', 'minuman', 'barista', 3],
            ['Pastry & Bakery', 'cemilan', 'kitchen', 4],
            ['Makanan Berat', 'makanan', 'kitchen', 5],
        ];
        foreach ($rows as [$name, $group, $station, $order]) {
            MenuCategory::query()->updateOrCreate(['name' => $name], ['display_group' => $group, 'station' => $station, 'sort_order' => $order]);
        }
    }
}
