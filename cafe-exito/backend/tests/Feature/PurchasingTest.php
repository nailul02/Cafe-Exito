<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;

/** Pembelian ≠ penerimaan: stok hanya bertambah saat penerimaan DIKONFIRMASI. */
class PurchasingTest extends ApiTestCase
{
    private function draftPurchase(float $qty = 10): array
    {
        $supplier = Supplier::factory()->create();
        $ingredient = Ingredient::factory()->create(['name' => 'Kopi', 'stock' => 100]);
        $id = $this->postJson(self::API.'/purchasing/purchases', [
            'supplier_id' => $supplier->id, 'details' => [['ingredient_id' => $ingredient->id, 'qty_ordered' => $qty, 'unit_price' => 190]],
        ])->assertCreated()->assertJsonPath('data.status.code', 'draft')->json('data.id');

        return [$id, $ingredient];
    }

    public function test_pembelian_dan_draft_tidak_menambah_stok_hanya_konfirmasi_yang_menambah(): void
    {
        $this->actingAsRole(UserRole::Admin);
        [$purchaseId, $ingredient] = $this->draftPurchase(10);
        $this->postJson(self::API."/purchasing/purchases/$purchaseId/place")->assertOk()->assertJsonPath('data.status.code', 'dipesan');
        $this->assertSame('100.000', $ingredient->refresh()->stock, 'status dipesan tidak boleh menambah stok');

        $detailId = Purchase::find($purchaseId)->details()->first()->id;
        $resp = $this->postJson(self::API."/purchasing/purchases/$purchaseId/receipts", ['details' => [['purchase_detail_id' => $detailId, 'qty_received' => 4]]])->assertCreated();
        $receiptId = $resp->json('data.receipts.0.id');
        $this->assertSame('100.000', $ingredient->refresh()->stock, 'draft penerimaan tidak boleh menambah stok');

        $this->postJson(self::API."/purchasing/receipts/$receiptId/confirm")->assertOk()->assertJsonPath('data.status.code', 'diterima_sebagian');
        $this->assertSame('104.000', $ingredient->refresh()->stock);
        $this->assertSame(1, StockMovement::where('type', 'penerimaan_pembelian')->count());

        $this->postJson(self::API."/purchasing/receipts/$receiptId/confirm")->assertStatus(409)->assertJsonPath('error.code', 'RECEIPT_ALREADY_CONFIRMED');
        $this->assertSame('104.000', $ingredient->refresh()->stock, 'konfirmasi ganda tidak boleh menambah stok dua kali');

        $second = $this->postJson(self::API."/purchasing/purchases/$purchaseId/receipts", ['details' => [['purchase_detail_id' => $detailId, 'qty_received' => 6]]])->assertCreated()->json('data.receipts.1.id');
        $this->postJson(self::API."/purchasing/receipts/$second/confirm")->assertOk()->assertJsonPath('data.status.code', 'diterima_lengkap');
        $this->assertSame('110.000', $ingredient->refresh()->stock);
    }

    public function test_penerimaan_melebihi_sisa_pesanan_ditolak(): void
    {
        $this->actingAsRole(UserRole::Admin);
        [$purchaseId] = $this->draftPurchase(10);
        $this->postJson(self::API."/purchasing/purchases/$purchaseId/place")->assertOk();
        $detailId = Purchase::find($purchaseId)->details()->first()->id;
        $this->postJson(self::API."/purchasing/purchases/$purchaseId/receipts", ['details' => [['purchase_detail_id' => $detailId, 'qty_received' => 11]]])
            ->assertStatus(409)->assertJsonPath('error.code', 'OVER_RECEIPT');
    }

    public function test_draft_pembelian_belum_boleh_menerima_barang(): void
    {
        $this->actingAsRole(UserRole::Admin);
        [$purchaseId] = $this->draftPurchase(10);
        $detailId = Purchase::find($purchaseId)->details()->first()->id;
        $this->postJson(self::API."/purchasing/purchases/$purchaseId/receipts", ['details' => [['purchase_detail_id' => $detailId, 'qty_received' => 1]]])
            ->assertStatus(409)->assertJsonPath('error.code', 'PURCHASE_NOT_EDITABLE');
    }

    public function test_pembelian_tidak_bisa_dibatalkan_setelah_ada_penerimaan_terkonfirmasi(): void
    {
        $this->actingAsRole(UserRole::Admin);
        [$purchaseId] = $this->draftPurchase(10);
        $this->postJson(self::API."/purchasing/purchases/$purchaseId/place")->assertOk();
        $detailId = Purchase::find($purchaseId)->details()->first()->id;
        $rid = $this->postJson(self::API."/purchasing/purchases/$purchaseId/receipts", ['details' => [['purchase_detail_id' => $detailId, 'qty_received' => 2]]])->json('data.receipts.0.id');
        $this->postJson(self::API."/purchasing/receipts/$rid/confirm")->assertOk();
        $this->postJson(self::API."/purchasing/purchases/$purchaseId/cancel")->assertStatus(409)->assertJsonPath('error.code', 'PURCHASE_NOT_EDITABLE');
    }

    public function test_manager_dengan_izin_terima_barang_dan_role_lain_ditolak(): void
    {
        $this->actingAsRole(UserRole::Kitchen);
        $this->postJson(self::API.'/purchasing/receipts/1/confirm')->assertStatus(403);
    }
}
