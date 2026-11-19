<?php

namespace Tests\Feature;

use App\Enums\UserRole;

/** Inventory and purchasing are retired operational modules; no API route is exposed. */
class PurchasingTest extends ApiTestCase
{
    public function test_inventory_purchasing_receipts_and_stock_adjustments_are_not_exposed(): void
    {
        $this->actingAsRole(UserRole::Manager);

        $this->getJson(self::API.'/inventory/ingredients')->assertNotFound();
        $this->postJson(self::API.'/inventory/stock-adjustments', [])->assertNotFound();
        $this->getJson(self::API.'/purchasing/suppliers')->assertNotFound();
        $this->postJson(self::API.'/purchasing/purchases', [])->assertNotFound();
        $this->postJson(self::API.'/purchasing/receipts/1/confirm')->assertNotFound();
        $this->getJson(self::API.'/reports/stock-usage')->assertNotFound();
    }
}
