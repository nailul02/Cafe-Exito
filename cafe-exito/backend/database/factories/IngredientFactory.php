<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ingredient> */
class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'category' => 'Bahan Makanan', 'unit' => 'g', 'stock' => 1000, 'min_stock' => 200, 'is_active' => true];
    }

    public function stock(float $stock): static
    {
        return $this->state(['stock' => $stock]);
    }
}
