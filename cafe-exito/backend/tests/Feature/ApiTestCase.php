<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Dasar tes fitur. MEMBUTUHKAN PostgreSQL khusus tes (database cafe_exito_test, lihat phpunit.xml).
 * Belum pernah dijalankan oleh pembuat awal (lingkungan tanpa Composer) — jalankan `composer test` setelah setup.
 */
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected const API = '/api/v1';

    protected function actingAsRole(UserRole $role, array $attrs = []): User
    {
        $user = User::factory()->role($role)->create($attrs);
        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    protected function table(string $number = '1'): Table
    {
        return Table::factory()->create(['table_number' => $number]);
    }

    /** Menu dengan resep: kebutuhan [nama bahan => [stok, jumlah per porsi]]. */
    protected function menuWithRecipe(string $name, array $needs, bool $kitchen = false, int $price = 20000): Menu
    {
        $menu = $kitchen ? Menu::factory()->kitchen()->create(['name' => $name, 'price' => $price]) : Menu::factory()->create(['name' => $name, 'price' => $price]);
        foreach ($needs as $ingredientName => [$stock, $perPortion]) {
            $ingredient = Ingredient::factory()->create(['name' => $ingredientName, 'stock' => $stock, 'min_stock' => 10]);
            $menu->recipes()->create(['ingredient_id' => $ingredient->id, 'quantity' => $perPortion]);
        }

        return $menu;
    }

    protected function placeOrder(array $items, string $method = 'tunai', string $table = '1', array $headers = [])
    {
        return $this->postJson(self::API.'/customer/orders', [
            'table_number' => $table, 'payment_method' => $method, 'items' => $items,
        ], $headers);
    }

    protected function category(string $name, string $group, string $station): MenuCategory
    {
        return MenuCategory::query()->firstOrCreate(['name' => $name], ['display_group' => $group, 'station' => $station]);
    }
}
