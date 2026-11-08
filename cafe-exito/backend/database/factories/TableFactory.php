<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Table> */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return ['table_number' => (string) fake()->unique()->numberBetween(1, 99), 'qr_token' => Str::lower(Str::random(24)), 'status' => 'active'];
    }
}
