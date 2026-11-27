<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/** Tracks which portion of each order line is represented by a payment installment. */
class PaymentAllocationService
{
    /** @param array<int, int> $allocations order_detail_id => amount */
    public static function attach(Payment $payment, array $allocations): void
    {
        $now = now();
        $rows = [];
        foreach ($allocations as $detailId => $amount) {
            if ($amount < 1) continue;
            $rows[] = [
                'payment_id' => $payment->id,
                'order_detail_id' => $detailId,
                'amount' => $amount,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows) DB::table('payment_order_details')->insert($rows);
    }

    /**
     * Assign a partial verification to order lines in ascending line order, leaving
     * the unpaid amounts on the original pending installment for the caller to copy.
     *
     * @return array<int, int> order_detail_id => remaining amount
     */
    public static function splitPending(Payment $payment, int $amountApplied): array
    {
        $rows = DB::table('payment_order_details')->where('payment_id', $payment->id)
            ->orderBy('order_detail_id')->lockForUpdate()->get();
        $remainingToApply = $amountApplied;
        $remainder = [];
        foreach ($rows as $row) {
            $allocated = (int) $row->amount;
            $paid = min($allocated, $remainingToApply);
            $remainingToApply -= $paid;
            $due = $allocated - $paid;
            if ($paid === 0) {
                DB::table('payment_order_details')->where('payment_id', $payment->id)
                    ->where('order_detail_id', $row->order_detail_id)->delete();
            } else {
                DB::table('payment_order_details')->where('payment_id', $payment->id)
                    ->where('order_detail_id', $row->order_detail_id)->update(['amount' => $paid, 'updated_at' => now()]);
            }
            if ($due > 0) $remainder[(int) $row->order_detail_id] = $due;
        }

        if ($remainingToApply > 0) {
            throw new \LogicException('Verified payment amount exceeds its item allocations.');
        }
        return $remainder;
    }

    /** Amount of a line allocation assigned to a one-based, inclusive unit range. */
    public static function amountForRange(int $amount, int $totalUnits, int $start, int $end): int
    {
        if ($amount <= 0 || $totalUnits <= 0 || $start < 1 || $end < $start) return 0;
        $start = min($start, $totalUnits + 1);
        $end = min($end, $totalUnits);
        if ($end < $start) return 0;
        return intdiv($amount * $end, $totalUnits) - intdiv($amount * ($start - 1), $totalUnits);
    }
}
