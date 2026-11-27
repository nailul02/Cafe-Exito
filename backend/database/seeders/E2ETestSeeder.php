<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class E2ETestSeeder extends Seeder
{
    /**
     * Dedicated seeder for Browser E2E test suite.
     * Enforces strict safety guard: the explicitly approved local project database.
     */
    public function run(): void
    {
        $connection = DB::connection();
        $host = $connection->getConfig('host');
        $port = (string) $connection->getConfig('port');
        $dbName = $connection->getDatabaseName();
        $dbUser = $connection->getConfig('username');
        $schema = DB::selectOne('SELECT current_schema() AS schema')->schema;
        $server = DB::selectOne('SELECT inet_server_addr() AS server')->server;
        $env = app()->environment();

        if ($dbName !== 'cafe_exito' || $schema !== 'public' || $env !== 'testing'
            || $host !== '127.0.0.1' || $server !== '127.0.0.1' || $port !== '5432' || $dbUser !== 'cafe_exito_user') {
            throw new RuntimeException("ABORT: E2ETestSeeder is restricted to approved local cafe_exito.public (127.0.0.1:5432) in testing env! Current target: [{$dbName}.{$schema}] on [{$host}:{$port}], env: [{$env}]");
        }

        // 1. Ensure Tables 1-10 exist
        foreach (range(1, 10) as $num) {
            Table::query()->firstOrCreate(
                ['table_number' => (string) $num],
                ['qr_token' => $num === 1 ? 'demo-table-01' : 'table_qr_token_' . str_pad((string) $num, 2, '0', STR_PAD_LEFT) . '_' . Str::lower(Str::random(12)), 'status' => 'active']
            );
        }

        // 2. Ensure Menu Categories exist
        $categories = [
            ['Signature Espresso', 'minuman', 'barista', 1],
            ['Manual Brew', 'minuman', 'barista', 2],
            ['Non-Coffee & Tea', 'minuman', 'barista', 3],
            ['Pastry & Bakery', 'cemilan', 'kitchen', 4],
            ['Makanan Berat', 'makanan', 'kitchen', 5],
        ];
        $catMap = [];
        foreach ($categories as [$name, $group, $station, $order]) {
            $attributes = ['display_group' => $group, 'station' => $station, 'sort_order' => $order];
            if (Schema::hasColumn('menu_categories', 'slug')) $attributes['slug'] = Str::slug($name);
            if (Schema::hasColumn('menu_categories', 'item_type_mapping')) {
                $attributes['item_type_mapping'] = $group === 'minuman' ? 'drink' : ($group === 'cemilan' ? 'snack' : 'food');
            }
            $cat = MenuCategory::query()->firstOrCreate(
                ['name' => $name],
                $attributes
            );
            $catMap[$name] = $cat;
        }

        // 3. Ensure Standard Menus exist
        $menus = [
            ['COF-E2E-01', 'Es Kopi Susu E2E', 'Signature Espresso', 18000, 'barista'],
            ['COF-E2E-02', 'Cappuccino E2E', 'Signature Espresso', 22000, 'barista'],
            ['TEA-E2E-01', 'Es Teh Manis E2E', 'Non-Coffee & Tea', 8000, 'barista'],
            ['PST-E2E-01', 'Croissant E2E', 'Pastry & Bakery', 22000, 'kitchen'],
            ['FOD-E2E-01', 'Nasi Goreng E2E', 'Makanan Berat', 30000, 'kitchen'],
        ];
        foreach ($menus as [$sku, $name, $catName, $price, $station]) {
            Menu::query()->firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'category_id' => $catMap[$catName]->id,
                    'price' => $price,
                    'station' => $station,
                    'is_available' => true,
                ]
            );
        }

        // 4. Dedicated E2E Test Staff Accounts
        $testPassword = env('E2E_STAFF_PASSWORD');
        if (! is_string($testPassword) || $testPassword === '') {
            throw new \RuntimeException('E2E_STAFF_PASSWORD must be configured before seeding E2E staff accounts.');
        }

        $staff = [
            ['E2E Kasir Staff', 'e2e_cashier', 'e2e_cashier@cafeexito.test', UserRole::Kasir, null],
            ['E2E Barista Staff', 'e2e_barista', 'e2e_barista@cafeexito.test', UserRole::BaristaKitchen, 'barista'],
            ['E2E Kitchen Staff', 'e2e_kitchen', 'e2e_kitchen@cafeexito.test', UserRole::BaristaKitchen, 'kitchen'],
            ['E2E Owner Staff', 'e2e_owner', 'e2e_owner@cafeexito.test', UserRole::Owner, null],
            ['E2E Manager Staff', 'e2e_manager', 'e2e_manager@cafeexito.test', UserRole::Manager, null],
        ];

        foreach ($staff as [$name, $username, $email, $role, $station]) {
            User::query()->firstOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => $testPassword,
                    'role' => $role,
                    'station' => $station,
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('E2ETestSeeder finished successfully on approved local cafe_exito database.');
    }
}
