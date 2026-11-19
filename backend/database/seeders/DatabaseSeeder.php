<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
            TableSeeder::class,
            MenuCategorySeeder::class,
            IngredientSeeder::class,
            MenuSeeder::class,
            SupplierSeeder::class,
        ]);
    }
}
