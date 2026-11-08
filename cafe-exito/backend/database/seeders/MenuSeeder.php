<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

/** Menu + resep contoh (BOM). Menu tanpa resep tidak mengurangi stok (has_recipe=false). */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['COF-001', 'Es Kopi Susu', 'Signature Espresso', 18000, [['Biji Kopi House Blend', 18], ['Fresh Milk Greenfields', 120], ['Gula Pasir', 15]]],
            ['COF-002', 'Cappuccino', 'Signature Espresso', 22000, [['Biji Kopi House Blend', 18], ['Fresh Milk Greenfields', 150]]],
            ['COF-003', 'Americano', 'Signature Espresso', 18000, [['Biji Kopi House Blend', 18]]],
            ['COF-004', 'Caramel Macchiato', 'Signature Espresso', 28000, [['Biji Kopi House Blend', 18], ['Fresh Milk Greenfields', 150], ['Sirup Karamel', 20]]],
            ['COF-005', 'Oat Latte', 'Signature Espresso', 27000, [['Biji Kopi House Blend', 18], ['Oat Milk Barista', 150]]],
            ['MBR-001', 'V60 Ethiopia Guji', 'Manual Brew', 30000, [['Biji Kopi House Blend', 15]]],
            ['NCO-001', 'Matcha Latte', 'Non-Coffee & Tea', 25000, [['Matcha Powder', 10], ['Fresh Milk Greenfields', 180]]],
            ['NCO-002', 'Es Teh Manis', 'Non-Coffee & Tea', 8000, [['Teh Hitam', 5], ['Gula Pasir', 20]]],
            ['PST-001', 'Butter Croissant', 'Pastry & Bakery', 22000, [['Butter Croissant Frozen', 1]]],
            ['PST-002', 'Double Choco Muffin', 'Pastry & Bakery', 24000, []],
            ['SNK-001', 'Kentang Goreng', 'Pastry & Bakery', 18000, []],
            ['FOD-001', 'Nasi Goreng Exito', 'Makanan Berat', 30000, [['Nasi Putih', 200], ['Telur', 1]]],
            ['FOD-002', 'Mie Goreng', 'Makanan Berat', 28000, []],
        ];
        foreach ($menus as [$sku, $name, $categoryName, $price, $recipe]) {
            $category = MenuCategory::query()->where('name', $categoryName)->firstOrFail();
            $menu = Menu::query()->firstOrCreate(['sku' => $sku], ['name' => $name, 'category_id' => $category->id, 'price' => $price, 'station' => $category->station->value, 'is_available' => true]);
            if ($menu->recipes()->count() === 0) {
                foreach ($recipe as [$ingredient, $qty]) {
                    $menu->recipes()->create(['ingredient_id' => Ingredient::query()->where('name', $ingredient)->firstOrFail()->id, 'quantity' => $qty]);
                }
            }
        }
    }
}
