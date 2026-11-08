<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['PT Sukses Berkah Kopi', '0751-555-0101'], ['PT Greenfields Dairy', '0751-555-0102'], ['Toko Bahan Roti Sentosa', '0751-555-0103']] as [$name, $phone]) {
            Supplier::query()->firstOrCreate(['name' => $name], ['phone' => $phone, 'address' => 'Padang', 'is_active' => true]);
        }
    }
}
