<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

/** @extends Factory<Menu> */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('???-###')),
            'name' => fake()->unique()->words(2, true),
            'category_id' => function () {
                $attributes = ['display_group' => 'minuman', 'station' => 'barista'];
                if (Schema::hasColumn('menu_categories', 'slug')) $attributes['slug'] = Str::slug('Signature Espresso');
                if (Schema::hasColumn('menu_categories', 'item_type_mapping')) $attributes['item_type_mapping'] = 'drink';
                return MenuCategory::query()->firstOrCreate(['name' => 'Signature Espresso'], $attributes)->id;
            },
            'price' => 18000,
            'station' => 'barista',
            'is_available' => true,
        ];
    }

    public function kitchen(): static
    {
        return $this->state(fn () => [
            'station' => 'kitchen',
            'category_id' => MenuCategory::query()->firstOrCreate(['name' => 'Makanan Berat'], (function () {
                $attributes = ['display_group' => 'makanan', 'station' => 'kitchen'];
                if (Schema::hasColumn('menu_categories', 'slug')) $attributes['slug'] = Str::slug('Makanan Berat');
                if (Schema::hasColumn('menu_categories', 'item_type_mapping')) $attributes['item_type_mapping'] = 'food';
                return $attributes;
            })())->id,
        ]);
    }
}
