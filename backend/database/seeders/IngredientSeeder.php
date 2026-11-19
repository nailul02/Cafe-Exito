<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

/** Data contoh (bukan stok nyata). Satuan: g | ml | pcs. */
class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Biji Kopi House Blend', 'Biji Kopi & Bubuk', 'g', 1800, 5000],
            ['Fresh Milk Greenfields', 'Susu & Dairy', 'ml', 4000, 12000],
            ['Oat Milk Barista', 'Susu & Dairy', 'ml', 14000, 8000],
            ['Sirup Karamel', 'Sirup & Topping', 'ml', 0, 2000],
            ['Sirup Vanilla', 'Sirup & Topping', 'ml', 2200, 3000],
            ['Matcha Powder', 'Biji Kopi & Bubuk', 'g', 600, 300],
            ['Butter Croissant Frozen', 'Bahan Makanan', 'pcs', 18, 10],
            ['Nasi Putih', 'Bahan Makanan', 'g', 9000, 4000],
            ['Telur', 'Bahan Makanan', 'pcs', 60, 30],
            ['Paper Cup 12oz + Lid', 'Packaging & Cups', 'pcs', 120, 150],
            ['Teh Hitam', 'Biji Kopi & Bubuk', 'g', 800, 300],
            ['Gula Pasir', 'Bahan Makanan', 'g', 5000, 2000],
        ];
        foreach ($rows as [$name, $category, $unit, $stock, $min]) {
            Ingredient::query()->firstOrCreate(['name' => $name], ['category' => $category, 'unit' => $unit, 'stock' => $stock, 'min_stock' => $min, 'is_active' => true]);
        }
    }
}
