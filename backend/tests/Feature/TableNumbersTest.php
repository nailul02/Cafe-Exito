<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Table;
use Database\Seeders\TableSeeder;

class TableNumbersTest extends ApiTestCase
{
    public function test_admin_list_contains_exactly_tables_one_through_fifteen(): void
    {
        (new TableSeeder())->run();
        $this->actingAsRole(UserRole::Manager);

        $numbers = collect($this->getJson(self::API.'/admin/tables')->assertOk()->json('data'))
            ->pluck('table_number')->all();

        $this->assertSame(array_map('strval', range(1, 15)), $numbers);
        $this->assertCount(15, array_unique($numbers));
        $this->assertSame(15, Table::query()->numbered()->where('status', 'active')->count());

        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/cashier/tables')->assertOk()
            ->assertJsonCount(15, 'data')->assertJsonPath('data.0.table_number', '1')
            ->assertJsonPath('data.14.table_number', '15');
    }

    public function test_qr_tokens_for_table_one_and_fifteen_resolve_to_their_own_tables(): void
    {
        (new TableSeeder())->run();
        foreach (['1', '15'] as $number) {
            $table = Table::query()->where('table_number', $number)->firstOrFail();
            $this->getJson(self::API.'/customer/tables/'.$table->qr_token)
                ->assertOk()->assertJsonPath('data.number', $number);
        }

        $this->getJson(self::API.'/customer/tables/not-a-real-table-token')
            ->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
    }

    public function test_public_table_resolver_accepts_only_qr_tokens_not_table_numbers(): void
    {
        (new TableSeeder())->run();
        foreach (range(1, 15) as $number) {
            $active = Table::query()->where('table_number', (string) $number)->firstOrFail();
            $this->getJson(self::API.'/customer/tables/'.$active->qr_token)
                ->assertOk()->assertJsonPath('data.number', (string) $number);
            $this->getJson(self::API.'/customer/tables/'.$number)
                ->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
        }

        $this->getJson(self::API.'/customer/tables/16')->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
    }

    public function test_all_fifteen_qr_tokens_are_unique_and_map_to_their_table(): void
    {
        (new TableSeeder())->run();
        $tables = Table::query()->numbered()->orderByRaw('CAST(table_number AS INTEGER)')->get();
        $this->assertCount(15, $tables);
        $this->assertCount(15, $tables->pluck('qr_token')->unique());

        foreach ($tables as $table) {
            $this->getJson(self::API.'/customer/tables/'.$table->qr_token)
                ->assertOk()->assertJsonPath('data.number', $table->table_number);
        }
    }

    public function test_qr_order_saves_correct_table_and_staff_resources_keep_the_number(): void
    {
        (new TableSeeder())->run();
        $table = Table::query()->where('table_number', '15')->firstOrFail();
        $menu = $this->menuWithRecipe('Kopi QR Meja 15', ['Kopi QR' => [1000, 18]]);
        $food = $this->menuWithRecipe('Roti QR Meja 15', ['Tepung QR' => [1000, 20]], true);

        $response = $this->placeOrder([
            ['menu_id' => $menu->id, 'qty' => 1], ['menu_id' => $food->id, 'qty' => 1],
        ], 'tunai', '15')->assertCreated()
            ->assertJsonPath('data.table.number', '15');

        $order = Order::query()->where('public_token', $response->json('data.public_token'))->firstOrFail();
        $this->assertSame($table->id, $order->table_id);

        $this->actingAsRole(UserRole::Kasir);
        $this->getJson(self::API.'/cashier/orders/'.$order->id)
            ->assertOk()->assertJsonPath('data.table.number', '15');

        $this->postJson(self::API.'/cashier/payments/'.$order->payment->id.'/verify')->assertOk();
        $this->actingAsRole(UserRole::BaristaKitchen);
        $baristaTicket = $order->tickets()->where('station', 'barista')->firstOrFail();
        $this->getJson(self::API.'/station/barista/tickets')
            ->assertOk()->assertJsonFragment(['id' => $baristaTicket->id, 'table_number' => '15']);
        $this->actingAsRole(UserRole::BaristaKitchen, ['station' => 'kitchen']);
        $kitchenTicket = $order->tickets()->where('station', 'kitchen')->firstOrFail();
        $this->getJson(self::API.'/station/kitchen/tickets')
            ->assertOk()->assertJsonFragment(['id' => $kitchenTicket->id, 'table_number' => '15']);
    }

    public function test_qr_checkout_meja_satu_saves_the_meja_satu_reference(): void
    {
        (new TableSeeder())->run();
        $table = Table::query()->where('table_number', '1')->firstOrFail();
        $menu = $this->menuWithRecipe('Kopi QR Meja 1', ['Kopi QR Meja 1' => [1000, 18]]);

        $response = $this->placeOrder([['menu_id' => $menu->id, 'qty' => 1]], 'tunai', '1')->assertCreated()
            ->assertJsonPath('data.table.id', $table->id)
            ->assertJsonPath('data.table.number', '1');

        $this->assertSame($table->id, Order::query()->where('public_token', $response->json('data.public_token'))->value('table_id'));
    }

    public function test_customer_rejects_table_number_sixteen(): void
    {
        $this->getJson(self::API.'/customer/tables/16')->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
    }

    public function test_customer_order_requires_a_validated_qr_and_rejects_table_manipulation(): void
    {
        $table = $this->table('1');
        $other = $this->table('2');
        $menu = $this->menuWithRecipe('Kopi Batas QR', ['Kopi Batas QR' => [1000, 18]]);
        $body = ['payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]], 'client_request_id' => (string) \Illuminate\Support\Str::uuid()];
        $ordersBefore = Order::query()->count();
        $this->postJson(self::API.'/customer/sessions/orders', $body)->assertUnauthorized();
        $opened = $this->postJson(self::API.'/customer/sessions/open', ['qr_token' => $table->qr_token, 'customer_name' => 'Test'])->assertOk();
        $this->postJson(self::API.'/customer/sessions/orders', [...$body, 'table_number' => '2'], ['X-Table-Session' => $opened->json('data.session_token')])
            ->assertStatus(422)->assertJsonPath('error.code', 'TABLE_INVALID');
        $this->assertSame($ordersBefore, Order::query()->count());
        $this->assertNotSame($table->qr_token, $other->qr_token);
    }

    public function test_rerunning_table_seeder_keeps_exactly_fifteen_rows_and_existing_qr_tokens(): void
    {
        (new TableSeeder())->run();
        $token = Table::query()->where('table_number', '15')->value('qr_token');
        (new TableSeeder())->run();

        $this->assertSame(15, Table::query()->numbered()->count());
        $this->assertDatabaseHas('tables', ['table_number' => '99']);
        $this->assertSame($token, Table::query()->where('table_number', '15')->value('qr_token'));
    }

    public function test_pos_requires_table_id_for_dine_in_and_saves_the_selected_table(): void
    {
        (new TableSeeder())->run();
        $this->actingAsRole(UserRole::Kasir);
        $menu = $this->menuWithRecipe('Menu POS meja', ['Bahan POS meja' => [1000, 10]]);

        $base = ['client_request_id' => '976ee0e6-2e57-488e-b558-59ab7b8f8e87', 'service_type' => 'dine_in', 'payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]]];
        $this->postJson(self::API.'/cashier/pos/orders', $base)->assertUnprocessable()->assertJsonValidationErrors('table_id');

        foreach (['1', '8', '15'] as $number) {
            $table = Table::query()->where('table_number', $number)->firstOrFail();
            $response = $this->postJson(self::API.'/cashier/pos/orders', [
                ...$base,
                'client_request_id' => (string) \Illuminate\Support\Str::uuid(),
                'table_id' => $table->id,
            ])->assertCreated()->assertJsonPath('data.table.id', $table->id)
                ->assertJsonPath('data.table.number', $number);
            $this->assertSame($table->id, Order::query()->findOrFail($response->json('data.id'))->table_id);
        }
    }

    public function test_pos_takeaway_has_no_table_and_invalid_or_inactive_table_ids_are_rejected(): void
    {
        (new TableSeeder())->run();
        $menu = $this->menuWithRecipe('Menu POS takeaway', ['Bahan POS takeaway' => [1000, 10]]);
        $this->actingAsRole(UserRole::Kasir);
        $base = ['client_request_id' => '', 'payment_method' => 'tunai', 'items' => [['menu_id' => $menu->id, 'qty' => 1]]];

        $this->postJson(self::API.'/cashier/pos/orders', [...$base, 'service_type' => 'dine_in', 'table_id' => 999999])
            ->assertUnprocessable()->assertJsonValidationErrors('table_id');
        $inactive = Table::query()->where('table_number', '14')->firstOrFail();
        $inactive->update(['status' => 'inactive']);
        $this->postJson(self::API.'/cashier/pos/orders', [...$base, 'client_request_id' => (string) \Illuminate\Support\Str::uuid(), 'service_type' => 'dine_in', 'table_id' => $inactive->id])
            ->assertUnprocessable()->assertJsonValidationErrors('table_id');
        $this->postJson(self::API.'/cashier/pos/orders', [...$base, 'client_request_id' => (string) \Illuminate\Support\Str::uuid(), 'service_type' => 'takeaway'])
            ->assertCreated()->assertJsonPath('data.table', null)->assertJsonPath('data.service_type', 'takeaway');
    }

    public function test_seeder_reactivates_numbered_tables_without_changing_historical_order_reference(): void
    {
        (new TableSeeder())->run();
        $table = Table::query()->where('table_number', '7')->firstOrFail();
        $order = $this->placeOrder([['menu_id' => $this->menuWithRecipe('Menu historis meja', ['Bahan historis' => [1000, 10]])->id, 'qty' => 1]], 'tunai', '7')->assertCreated();
        $savedOrder = Order::query()->where('public_token', $order->json('data.public_token'))->firstOrFail();
        $table->update(['status' => 'inactive']);

        (new TableSeeder())->run();

        $this->assertSame($table->id, $savedOrder->refresh()->table_id);
        $this->assertSame('7', $savedOrder->table->table_number);
        $this->assertSame('active', $table->refresh()->status);
        $this->assertSame(15, Table::query()->numbered()->where('status', 'active')->count());
    }
}
