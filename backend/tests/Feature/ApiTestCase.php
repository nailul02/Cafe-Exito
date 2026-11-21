<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $defaults = ['station' => $role === UserRole::BaristaKitchen ? 'barista' : null];
        $user = User::factory()->role($role)->create(array_merge($defaults, $attrs));
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
        $response = $this->postJson(self::API.'/customer/orders', [
            'table_number' => $table, 'payment_method' => $method, 'items' => $items,
        ], $headers);

        // QRIS Self-Order tests that exercise downstream payment behavior model the
        // customer's required prior proof upload. Dedicated proof tests cover the
        // missing-proof rejection path directly.
        if ($method === 'qris' && $response->isSuccessful()) {
            $token = $response->json('data.public_token');
            $payment = Order::where('public_token', $token)->firstOrFail()->payment;
            if (! $payment->proofs()->where('status', 'pending')->exists()) {
                Storage::fake('local');
                $proof = UploadedFile::fake()->image('qris-proof.png', 30, 30);
                file_put_contents($proof->getRealPath(), $token, FILE_APPEND);
                $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $proof])->assertCreated();
            }
        }

        return $response;
    }

    protected function uploadPaymentProof(string $token, string $fileName = 'proof.png'): void
    {
        $payment = Order::where('public_token', $token)->firstOrFail()->payment;
        if ($payment->proofs()->where('status', 'pending')->exists()) {
            return;
        }

        Storage::fake('local');
        $proof = UploadedFile::fake()->image($fileName, 20, 20);
        file_put_contents($proof->getRealPath(), $token, FILE_APPEND);
        $this->post(self::API."/customer/orders/$token/payment-proof", [
            'proof' => $proof,
        ])->assertCreated();
    }

    protected function category(string $name, string $group, string $station): MenuCategory
    {
        return MenuCategory::query()->firstOrCreate(['name' => $name], ['display_group' => $group, 'station' => $station]);
    }
}
