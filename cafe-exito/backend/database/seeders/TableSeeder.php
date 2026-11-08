<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 12) as $n) {
            Table::query()->firstOrCreate(['table_number' => (string) $n], ['qr_token' => Str::lower(Str::random(24)), 'status' => 'active']);
        }
    }
}
