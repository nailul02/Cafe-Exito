<?php

namespace App\Services;

use App\Exceptions\DomainError;
use App\Models\Table;
use App\Models\TableSession;
use App\Models\TableSessionToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TableSessionService
{
    public function open(string $qrToken, ?string $customerName = null): array
    {
        return DB::transaction(function () use ($qrToken, $customerName) {
            $table = Table::query()->numbered()->where('qr_token', $qrToken)->where('status', 'active')->lockForUpdate()->first();
            if (! $table) throw DomainError::tableInvalid('QR meja tidak dikenali atau meja tidak aktif.');

            $session = TableSession::query()->where('table_id', $table->id)->where('status', 'active')->lockForUpdate()->first();
            if (! $session && ! filled($customerName)) {
                return ['requires_name' => true, 'table' => ['number' => $table->table_number], 'session' => null, 'session_token' => null];
            }
            if (! $session) {
                $session = TableSession::query()->create([
                    'table_id' => $table->id, 'customer_name' => trim($customerName), 'status' => 'active', 'opened_at' => now(),
                ]);
            }

            $plainToken = Str::random(64);
            $session->tokens()->create(['token_hash' => hash('sha256', $plainToken)]);
            return ['requires_name' => false, 'table' => ['number' => $table->table_number], 'session' => $this->snapshot($session), 'session_token' => $plainToken];
        }, 3);
    }

    public function snapshot(TableSession $session): array
    {
        $session->load(['table', 'orders.details.cancellations', 'orders.payments.proofs', 'orders.payments.refunds']);
        $orders = $session->orders->sortBy('id')->values()->map(function ($order) {
            $verified = (int) $order->payments->filter(fn ($payment) => in_array($payment->status->value, ['verified', 'refunded'], true))->sum('amount');
            $refunded = (int) $order->payments->sum(fn ($payment) => (int) $payment->refunds->where('status', 'completed')->sum('amount'));
            $paid = max(0, $verified - $refunded);
            $total = (int) $order->total;
            return [
                'public_token' => $order->public_token,
                'order_number' => $order->order_number,
                'display_number' => $order->displayNumber(),
                'status' => $order->status->value,
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, $total - $paid),
                'items' => $order->details->map(fn ($detail) => [
                    'name' => $detail->menu_name,
                    'qty' => (int) $detail->qty,
                    'qty_cancelled' => (int) $detail->cancellations->sum('qty'),
                    'unit_price' => (int) $detail->unit_price,
                    'subtotal' => (int) $detail->subtotal,
                    'station' => $detail->station->value,
                ])->values()->all(),
                'payments' => $order->payments->sortBy('id')->values()->map(fn ($p) => [
                    'method' => $p->method->value, 'status' => $p->status->value, 'amount' => $p->amount,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                    'proof_status' => $p->proofs->sortByDesc('id')->first()?->status,
                ])->all(),
            ];
        });
        $total = $session->status === 'closed' && $session->closing_total !== null ? $session->closing_total : (int) $orders->sum('total');
        $paid = $session->status === 'closed' && $session->closing_paid !== null ? $session->closing_paid : (int) $orders->sum('paid');
        return [
            'table' => ['number' => $session->table->table_number],
            'customer_name' => $session->customer_name,
            'status' => $session->status,
            'opened_at' => $session->opened_at?->toIso8601String(),
            'closed_at' => $session->closed_at?->toIso8601String(),
            'total' => $total,
            'paid' => $paid,
            'balance' => $session->status === 'closed' && $session->closing_balance !== null ? $session->closing_balance : max(0, $total - $paid),
            'orders' => $orders->all(),
        ];
    }

    public function close(TableSession $session, User $cashier): TableSession
    {
        return DB::transaction(function () use ($session, $cashier) {
            $session = TableSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            if ($session->status !== 'active') throw new DomainError('TABLE_SESSION_CLOSED', 'Sesi ini sudah ditutup.', 409);
            $orders = $session->orders()->lockForUpdate()->with('payments')->get();
            if ($orders->contains(fn ($o) => ! in_array($o->status->value, ['selesai', 'dibatalkan'], true))) {
                throw new DomainError('SESSION_ORDERS_INCOMPLETE', 'Semua pesanan dalam sesi harus selesai diproses sebelum sesi ditutup.', 409);
            }
            if ($orders->contains(fn ($o) => $o->payments->contains(fn ($p) => $p->status->value === 'pending'))) {
                throw new DomainError('SESSION_PAYMENTS_PENDING', 'Selesaikan verifikasi seluruh pembayaran sebelum sesi ditutup.', 409);
            }
            $snapshot = $this->snapshot($session);
            if ($snapshot['balance'] > 0) throw new DomainError('SESSION_BALANCE_DUE', 'Sisa tagihan sesi harus lunas sebelum sesi ditutup.', 409, ['balance' => $snapshot['balance']]);
            $hasUnresolvedRefund = DB::table('refund_requests')->whereIn('order_id', $orders->pluck('id'))
                ->whereNotIn('status', ['completed', 'rejected', 'withdrawn'])->exists();
            if ($hasUnresolvedRefund) throw new DomainError('SESSION_REFUNDS_PENDING', 'Selesaikan transaksi refund yang masih terbuka sebelum menutup sesi.', 409);

            $session->update([
                'status' => 'closed', 'closed_at' => now(), 'closed_by' => $cashier->id,
                'closing_total' => $snapshot['total'], 'closing_paid' => $snapshot['paid'], 'closing_balance' => $snapshot['balance'],
            ]);
            $session->tokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            return $session->fresh('table');
        }, 3);
    }
}
