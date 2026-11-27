<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Atomic entry point for the explicitly approved Cafe Exito catalog update. */
class FinalMenuCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $identity = DB::selectOne('SELECT current_database() AS database');
        if (($identity->database ?? null) !== 'cafe_exito') {
            throw new RuntimeException('Refusing catalog seed: configured database is not cafe_exito.');
        }

        DB::transaction(function (): void {
            $this->call([MenuCategorySeeder::class, MenuSeeder::class]);
        });
    }
}
