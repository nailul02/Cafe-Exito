<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests run in per-test transactions against the approved local cafe_exito database.
 * Migrations are run separately before the suite.
 * Belum pernah dijalankan oleh pembuat awal (lingkungan tanpa Composer) — jalankan `composer test` setelah setup.
 */
abstract class ApiTestCase extends TestCase
{
    use DatabaseTransactions;

    protected const API = '/api/v1';
    protected ?string $customerSessionToken = null;

    protected function actingAsRole(UserRole $role, array $attrs = []): User
    {
        $defaults = ['station' => $role === UserRole::BaristaKitchen ? 'barista' : null];
        $user = User::factory()->role($role)->create(array_merge($defaults, $attrs));
        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    protected function table(string $number = '1'): Table
    {
        return Table::query()->firstOrCreate(
            ['table_number' => $number],
            ['qr_token' => Str::lower(Str::random(24)), 'status' => 'active'],
        );
    }

    /** Menu dengan resep: kebutuhan [nama bahan => [stok, jumlah per porsi]]. */
    protected function menuWithRecipe(string $name, array $needs, bool $kitchen = false, int $price = 20000): Menu
    {
        $menu = $kitchen ? Menu::factory()->kitchen()->create(['name' => $name, 'price' => $price]) : Menu::factory()->create(['name' => $name, 'price' => $price]);
        foreach ($needs as $ingredientName => [$stock, $perPortion]) {
            $ingredient = Ingredient::query()->where('name', $ingredientName)->first();
            if ($ingredient) {
                $ingredient->update(['stock' => $stock, 'min_stock' => 10]);
            } else {
                $ingredient = Ingredient::factory()->create(['name' => $ingredientName, 'stock' => $stock, 'min_stock' => 10]);
            }
            $menu->recipes()->create(['ingredient_id' => $ingredient->id, 'quantity' => $perPortion]);
        }

        return $menu;
    }

    protected function placeOrder(array $items, string $method = 'tunai', string $table = '1', array $headers = [], bool $uploadQrisProof = true)
    {
        $tableRecord = Table::query()->firstOrCreate(
            ['table_number' => $table],
            ['qr_token' => Str::lower(Str::random(24)), 'status' => 'active'],
        );
        $opened = $this->postJson(self::API.'/customer/sessions/open', [
            'qr_token' => $tableRecord->qr_token, 'customer_name' => 'Pelanggan Test',
        ])->assertOk();
        $this->customerSessionToken = $opened->json('data.session_token');
        $this->withHeader('X-Table-Session', $this->customerSessionToken);
        $response = $this->postJson(self::API.'/customer/sessions/orders', [
            'payment_method' => $method, 'items' => $items, 'client_request_id' => (string) Str::uuid(),
        ], array_merge(['X-Table-Session' => $this->customerSessionToken], $headers));

        // QRIS Self-Order tests that exercise downstream payment behavior model the
        // customer's required prior proof upload. Dedicated proof tests cover the
        // missing-proof rejection path directly.
        if ($uploadQrisProof && $method === 'qris' && $response->isSuccessful()) {
            $token = $response->json('data.public_token');
            $payment = Order::where('public_token', $token)->firstOrFail()->payment;
            if (! $payment->proofs()->where('status', 'pending')->exists()) {
                Storage::fake('local');
                $proof = UploadedFile::fake()->image('qris-proof.png', 30, 30);
                file_put_contents($proof->getRealPath(), $token, FILE_APPEND);
                $this->post(self::API.'/customer/orders/'.$token.'/payment-proof', ['proof' => $proof], ['X-Table-Session' => $this->customerSessionToken])->assertCreated();
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
        ], ['X-Table-Session' => $this->customerSessionToken])->assertCreated();
    }

    protected function category(string $name, string $group, string $station): MenuCategory
    {
        $attributes = ['display_group' => $group, 'station' => $station];
        if (\Illuminate\Support\Facades\Schema::hasColumn('menu_categories', 'slug')) $attributes['slug'] = Str::slug($name);
        if (\Illuminate\Support\Facades\Schema::hasColumn('menu_categories', 'item_type_mapping')) {
            $attributes['item_type_mapping'] = $group === 'minuman' ? 'drink' : ($group === 'cemilan' ? 'snack' : 'food');
        }
        return MenuCategory::query()->firstOrCreate(['name' => $name], $attributes);
    }
}
