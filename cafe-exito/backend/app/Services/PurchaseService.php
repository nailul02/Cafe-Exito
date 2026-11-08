<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\ReceiptStatus;
use App\Exceptions\DomainError;
use App\Models\Purchase;
use App\Models\User;
use App\Support\NumberGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Pembelian bahan baku dari supplier. Status pembelian TIDAK menambah stok;
 * stok hanya bertambah lewat GoodsReceiptService::confirm().
 */
class PurchaseService
{
    public function __construct(private readonly AuditService $audit) {}

    /** @param array{supplier_id:int, note?:?string, details:list<array{ingredient_id:int, qty_ordered:float|int|string, unit_price?:int}>} $data */
    public function create(array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($data, $user) {
            $purchase = Purchase::create([
                'purchase_number' => NumberGenerator::next('PO', 'purchases', 'purchase_number'),
                'supplier_id' => $data['supplier_id'],
                'ordered_by' => $user->id,
                'status' => PurchaseStatus::Draft->value,
                'note' => $data['note'] ?? null,
            ]);
            $purchase->details()->createMany($this->rows($data['details']));
            $this->audit->record($user, 'purchase.created', 'purchase', $purchase->id);

            return $purchase;
        });
    }

    /** Hanya draft yang boleh diubah. */
    public function update(Purchase $purchase, array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $data, $user) {
            $purchase = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            if ($purchase->status !== PurchaseStatus::Draft) {
                throw DomainError::purchaseNotEditable('Hanya draft pembelian yang dapat diubah.');
            }
            $purchase->update(['supplier_id' => $data['supplier_id'], 'note' => $data['note'] ?? null]);
            $purchase->details()->delete();
            $purchase->details()->createMany($this->rows($data['details']));
            $this->audit->record($user, 'purchase.updated', 'purchase', $purchase->id);

            return $purchase;
        });
    }

    public function place(Purchase $purchase, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $user) {
            $purchase = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            if (! $purchase->status->canTransitionTo(PurchaseStatus::Dipesan) || $purchase->status !== PurchaseStatus::Draft) {
                throw DomainError::invalidTransition('Pembelian', $purchase->status->value, PurchaseStatus::Dipesan->value);
            }
            $purchase->update(['status' => PurchaseStatus::Dipesan->value, 'ordered_at' => now()]);
            $this->audit->record($user, 'purchase.placed', 'purchase', $purchase->id);

            return $purchase;
        });
    }

    /** Dibatalkan selama belum ada penerimaan yang dikonfirmasi. */
    public function cancel(Purchase $purchase, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $user) {
            $purchase = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            $confirmed = $purchase->receipts()->where('status', ReceiptStatus::Dikonfirmasi->value)->exists();
            if ($confirmed || ! in_array($purchase->status, [PurchaseStatus::Draft, PurchaseStatus::Dipesan], true)) {
                throw DomainError::purchaseNotEditable('Pembelian tidak dapat dibatalkan: sudah ada penerimaan atau sudah selesai.');
            }
            $purchase->update(['status' => PurchaseStatus::Dibatalkan->value, 'cancelled_at' => now()]);
            $this->audit->record($user, 'purchase.cancelled', 'purchase', $purchase->id);

            return $purchase;
        });
    }

    /** @return list<array<string, mixed>> */
    private function rows(array $details): array
    {
        return array_map(static fn (array $d) => [
            'ingredient_id' => $d['ingredient_id'],
            'qty_ordered' => $d['qty_ordered'],
            'qty_received' => 0,
            'unit_price' => $d['unit_price'] ?? 0,
        ], array_values($details));
    }
}
