<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Menu> */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('???-###')),
            'name' => fake()->unique()->words(2, true),
            'category_id' => fn () => MenuCategory::query()->firstOrCreate(['name' => 'Signature Espresso'], ['display_group' => 'minuman', 'station' => 'barista'])->id,
            'price' => 18000,
            'station' => 'barista',
            'is_available' => true,
        ];
    }

    public function kitchen(): static
    {
        return $this->state(fn () => [
            'station' => 'kitchen',
            'category_id' => MenuCategory::query()->firstOrCreate(['name' => 'Makanan Berat'], ['display_group' => 'makanan', 'station' => 'kitchen'])->id,
        ]);
    }
}
