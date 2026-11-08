<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\ReceiptStatus;
use App\Exceptions\DomainError;
use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;
use App\Support\NumberGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Penerimaan barang. Draft tidak mengubah stok. confirm() adalah SATU transaksi yang:
 * mengunci penerimaan, pembelian, rincian pembelian dan bahan; memvalidasi sisa pesanan; menambah stok lewat StockService;
 * memperbarui qty_received dan status pembelian; lalu mengunci penerimaan (tidak dapat diubah setelahnya).
 */
class GoodsReceiptService
{
    public function __construct(private readonly StockService $stock, private readonly AuditService $audit) {}

    /** @param list<array{purchase_detail_id:int, qty_received:float|int|string}> $lines */
    public function createDraft(Purchase $purchase, array $lines, ?string $note, User $user, ?string $receivedAt = null): GoodsReceipt
    {
        return DB::transaction(function () use ($purchase, $lines, $note, $user, $receivedAt) {
            $purchase = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            if (! $purchase->status->canReceive()) {
                throw DomainError::purchaseNotEditable('Pembelian belum dipesan atau sudah selesai/dibatalkan.');
            }
            $this->validateLines($purchase, $lines);
            $receipt = GoodsReceipt::create([
                'receipt_number' => NumberGenerator::next('GR', 'goods_receipts', 'receipt_number'),
                'purchase_id' => $purchase->id,
                'status' => ReceiptStatus::Draft->value,
                'received_at' => $receivedAt ?? now()->toDateString(),
                'note' => $note,
                'created_by' => $user->id,
            ]);
            $receipt->details()->createMany($this->rows($lines));

            return $receipt;
        });
    }

    /** Ubah draft (setelah dikonfirmasi tidak bisa). */
    public function updateDraft(GoodsReceipt $receipt, array $lines, ?string $note): GoodsReceipt
    {
        return DB::transaction(function () use ($receipt, $lines, $note) {
            $receipt = GoodsReceipt::query()->whereKey($receipt->id)->lockForUpdate()->firstOrFail();
            if ($receipt->status === ReceiptStatus::Dikonfirmasi) {
                throw DomainError::receiptAlreadyConfirmed();
            }
            $this->validateLines($receipt->purchase, $lines);
            $receipt->update(['note' => $note]);
            $receipt->details()->delete();
            $receipt->details()->createMany($this->rows($lines));

            return $receipt;
        });
    }

    public function confirm(GoodsReceipt $receipt, User $user): GoodsReceipt
    {
        return DB::transaction(function () use ($receipt, $user) {
            $receipt = GoodsReceipt::query()->whereKey($receipt->id)->lockForUpdate()->firstOrFail();
            if ($receipt->status === ReceiptStatus::Dikonfirmasi) {
                throw DomainError::receiptAlreadyConfirmed();
            }
            $purchase = Purchase::query()->whereKey($receipt->purchase_id)->lockForUpdate()->firstOrFail();
            if (! $purchase->status->canReceive()) {
                throw DomainError::purchaseNotEditable('Pembelian belum dipesan atau sudah selesai/dibatalkan.');
            }

            $lines = $receipt->details()->get();
            $details = PurchaseDetail::query()->whereIn('id', $lines->pluck('purchase_detail_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($lines as $line) {
                if ((float) $line->qty_received > $details[$line->purchase_detail_id]->outstanding() + 1e-9) {
                    throw DomainError::overReceipt();
                }
            }
            // Urutan stok menurut ingredient_id agar tidak terjadi deadlock antar transaksi.
            foreach ($lines->sortBy(fn ($l) => $details[$l->purchase_detail_id]->ingredient_id) as $line) {
                $detail = $details[$line->purchase_detail_id];
                $this->stock->restock($line, $detail->ingredient_id, (float) $line->qty_received, $user);
                $detail->qty_received = round((float) $detail->qty_received + (float) $line->qty_received, 3);
                $detail->save();
            }

            $complete = $purchase->details()->get()->every(fn ($d) => (float) $d->qty_received + 1e-9 >= (float) $d->qty_ordered);
            $purchase->update(['status' => ($complete ? PurchaseStatus::DiterimaLengkap : PurchaseStatus::DiterimaSebagian)->value]);
            $receipt->update(['status' => ReceiptStatus::Dikonfirmasi->value, 'confirmed_by' => $user->id, 'confirmed_at' => now()]);
            $this->audit->record($user, 'receipt.confirmed', 'goods_receipt', $receipt->id, ['purchase_id' => $purchase->id]);

            return $receipt;
        });
    }

    /** @param list<array{purchase_detail_id:int, qty_received:float|int|string}> $lines */
    private function validateLines(Purchase $purchase, array $lines): void
    {
        $details = $purchase->details()->get()->keyBy('id');
        foreach ($lines as $line) {
            $detail = $details->get((int) $line['purchase_detail_id']);
            if (! $detail) {
                throw new DomainError('VALIDATION_FAILED', 'Rincian pembelian tidak termasuk pembelian ini.', 422);
            }
            if ((float) $line['qty_received'] <= 0 || (float) $line['qty_received'] > $detail->outstanding() + 1e-9) {
                throw DomainError::overReceipt();
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function rows(array $lines): array
    {
        return array_map(static fn (array $l) => ['purchase_detail_id' => $l['purchase_detail_id'], 'qty_received' => $l['qty_received']], array_values($lines));
    }
}
