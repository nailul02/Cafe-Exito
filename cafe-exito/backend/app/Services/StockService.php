<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\DomainError;
use App\Models\GoodsReceiptDetail;
use App\Models\Ingredient;
use App\Models\OrderTicket;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * SATU-SATUNYA tempat yang boleh mengubah ingredients.stock. Setiap perubahan menulis stock_movements.
 * Wajib dipanggil di dalam DB::transaction. Baris bahan dikunci berurutan menurut id (hindari deadlock).
 */
class StockService
{
    /**
     * Mengurangi stok untuk tiket yang mulai diproses.
     *
     * @param  array<int, float>  $requirements  ingredient_id => jumlah total yang dibutuhkan
     */
    public function consume(OrderTicket $ticket, array $requirements, ?User $user): void
    {
        $this->assertTransaction();
        if ($requirements === []) {
            return;
        }
        $rows = $this->lock(array_keys($requirements));
        $short = [];
        foreach ($requirements as $id => $qty) {
            if ((float) $rows[$id]->stock + 1e-9 < $qty) {
                $short[] = $rows[$id]->name;
            }
        }
        if ($short) {
            throw DomainError::insufficientStock($short);
        }
        ksort($requirements);
        foreach ($requirements as $id => $qty) {
            $this->apply($rows[$id], -$qty, StockMovementType::PemakaianPesanan, $user, ['order_ticket_id' => $ticket->id]);
        }
    }

    /** Mengembalikan pemakaian tiket yang dibatalkan (sekali saja). */
    public function reverse(OrderTicket $ticket, ?User $user): void
    {
        $this->assertTransaction();
        $already = StockMovement::where('order_ticket_id', $ticket->id)->where('type', StockMovementType::PembalikanPemakaian->value)->exists();
        if ($already) {
            return;
        }
        $used = StockMovement::where('order_ticket_id', $ticket->id)->where('type', StockMovementType::PemakaianPesanan->value)
            ->selectRaw('ingredient_id, SUM(-quantity_change) AS qty')->groupBy('ingredient_id')->pluck('qty', 'ingredient_id');
        if ($used->isEmpty()) {
            return;
        }
        $rows = $this->lock($used->keys()->all());
        foreach ($used->sortKeys() as $id => $qty) {
            $this->apply($rows[$id], (float) $qty, StockMovementType::PembalikanPemakaian, $user, ['order_ticket_id' => $ticket->id]);
        }
    }

    /** Menambah stok setelah penerimaan barang dikonfirmasi. */
    public function restock(GoodsReceiptDetail $detail, int $ingredientId, float $qty, User $user): void
    {
        $this->assertTransaction();
        $row = $this->lock([$ingredientId])[$ingredientId];
        $this->apply($row, $qty, StockMovementType::PenerimaanPembelian, $user, ['goods_receipt_detail_id' => $detail->id]);
    }

    /** Koreksi/stock opname. Catatan wajib; hasil tidak boleh negatif. */
    public function adjust(Ingredient $ingredient, float $change, User $user, string $note): Ingredient
    {
        $this->assertTransaction();
        $row = $this->lock([$ingredient->id])[$ingredient->id];
        if ((float) $row->stock + $change < 0) {
            throw DomainError::insufficientStock([$row->name]);
        }
        $this->apply($row, $change, StockMovementType::Koreksi, $user, ['note' => $note]);

        return $row->refresh();
    }

    /** @param list<int> $ids */
    private function lock(array $ids): Collection
    {
        sort($ids);

        return Ingredient::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
    }

    private function apply(Ingredient $row, float $change, StockMovementType $type, ?User $user, array $reference): void
    {
        $after = round((float) $row->stock + $change, 3);
        $row->stock = $after;
        $row->save();
        StockMovement::create(array_merge([
            'ingredient_id' => $row->id,
            'type' => $type->value,
            'quantity_change' => round($change, 3),
            'stock_after' => $after,
            'user_id' => $user?->id,
        ], $reference));
    }

    private function assertTransaction(): void
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('StockService harus dipanggil di dalam DB::transaction.');
        }
    }
}
