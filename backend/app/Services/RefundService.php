<?php

namespace App\Services;

use App\Exceptions\DomainError;
use App\Models\Order;
use App\Models\OrderItemCancellation;
use App\Models\Payment;
use App\Models\RefundConflictNote;
use App\Models\RefundEvent;
use App\Models\RefundExecution;
use App\Models\RefundRequest;
use App\Models\RefundRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(private readonly InAppNotificationService $notifications, private readonly AuditService $audit) {}

    public function requestForCancellation(OrderItemCancellation $c, Payment $payment): ?RefundRequest
    {
        return DB::transaction(function () use ($c, $payment) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status->value !== 'verified') {
                return null;
            }
            $amount = $c->unit_price * $c->qty;
            $reserved = (int) RefundRequest::query()->where('payment_id', $payment->id)->whereIn('status', ['requested', 'approved', 'completed'])->sum('amount');
            if ($reserved + $amount > (int) $payment->amount) {
                throw new DomainError('REFUND_LIMIT_EXCEEDED', 'Nilai refund melampaui nilai pembayaran.', 409);
            }
            $request = RefundRequest::query()->where('payment_id', $payment->id)->where('status', 'requested')->lockForUpdate()->first();
            $new = ! $request;
            if ($new) {
                $request = RefundRequest::create(['order_id' => $payment->order_id, 'payment_id' => $payment->id, 'source' => $c->source, 'status' => 'requested', 'amount' => $amount, 'reason' => $c->reason, 'requested_by' => $c->actor_id]);
            } else {
                $request->update(['amount' => $request->amount + $amount, 'reason' => mb_substr($request->reason.' | '.$c->reason, 0, 500), 'source' => $request->source === $c->source ? $request->source : 'mixed']);
            }
            RefundRequestItem::create(['refund_request_id' => $request->id, 'cancellation_id' => $c->id, 'qty' => $c->qty, 'amount' => $amount]);
            RefundEvent::create(['refund_request_id' => $request->id, 'actor_id' => $c->actor_id, 'actor_type' => $c->actor_type, 'event' => $new ? 'requested' : 'updated', 'details' => ['cancellation_id' => $c->id, 'amount' => $amount, 'reason' => $c->reason]]);
            $this->audit->record(null, 'refund.requested', 'refund_request', $request->id, ['source' => $c->source, 'actor_type' => $c->actor_type, 'order_id' => $payment->order_id, 'amount' => $request->amount, 'cancellation_id' => $c->id]);
            if ($new) {
                $this->notifications->sendRole('owner', 'refund.requested', ['refund_request_id' => $request->id, 'amount' => $request->amount]);
            }

            return $request->load('items.cancellation.detail');
        });
    }

    public function manualRequest(Payment $payment, User $cashier, int $amount, string $reason): RefundRequest
    {
        return DB::transaction(function () use ($payment, $cashier, $amount, $reason) {
            $orderId = Payment::whereKey($payment->id)->value('order_id');
            Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $p = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($p->status->value !== 'verified') {
                throw new DomainError('INVALID_REFUND_STATE', 'Refund manual hanya dapat diminta untuk pembayaran verified.');
            }$active = RefundRequest::query()->where('payment_id', $p->id)->where('status', 'requested')->lockForUpdate()->first();
            if ($active) {
                throw new DomainError('REFUND_REQUEST_ALREADY_PENDING', 'Payment ini sudah memiliki refund request yang menunggu keputusan Owner.', 409, ['refund_request_id' => $active->id]);
            }$reserved = (int) RefundRequest::where('payment_id', $p->id)->whereIn('status', ['requested', 'approved', 'completed'])->sum('amount');
            if ($amount < 1 || $reserved + $amount > (int) $p->amount) {
                throw new DomainError('REFUND_LIMIT_EXCEEDED', 'Nilai refund melampaui saldo pembayaran.', 409);
            }$r = RefundRequest::create(['order_id' => $p->order_id, 'payment_id' => $p->id, 'source' => 'manual', 'status' => 'requested', 'amount' => $amount, 'reason' => $reason, 'requested_by' => $cashier->id]);
            $this->event($r, $cashier, 'cashier', 'requested', ['amount' => $amount, 'manual' => true]);
            $this->notifications->sendRole('owner', 'refund.requested', ['refund_request_id' => $r->id, 'amount' => $r->amount]);
            $this->audit->record($cashier, 'refund.requested', 'refund_request', $r->id, ['amount' => $amount, 'manual' => true]);

            return $r;
        });
    }

    public function decide(RefundRequest $refund, User $owner, string $decision, ?string $reason = null): RefundRequest
    {
        return DB::transaction(function () use ($refund, $owner, $decision, $reason) {
            $paymentId = RefundRequest::whereKey($refund->id)->value('payment_id');
            $orderId = Payment::whereKey($paymentId)->value('order_id');
            Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            Payment::whereKey($paymentId)->lockForUpdate()->firstOrFail();
            $r = RefundRequest::query()->whereKey($refund->id)->lockForUpdate()->with('payment')->firstOrFail();
            if ($r->status !== 'requested') {
                throw new DomainError('INVALID_REFUND_STATE', 'Pengajuan bukan dalam status menunggu keputusan.');
            }
            if ((int) $r->payment->verified_by === (int) $owner->id || (int) $r->order->created_by === (int) $owner->id) {
                $r->update(['status' => 'conflict_of_interest', 'conflict_of_interest_at' => now()]);
                $this->event($r, $owner, 'owner', 'conflict_of_interest', []);
                $this->notifications->sendRole('manager', 'refund.conflict_of_interest', ['refund_request_id' => $r->id]);
                $this->audit->record($owner, 'refund.conflict_of_interest', 'refund_request', $r->id, []);

                return $r;
            }
            if ($decision === 'rejected' && trim((string) $reason) === '') {
                throw new DomainError('VALIDATION_FAILED', 'Alasan penolakan wajib diisi.', 422);
            }
            $r->update(['status' => $decision, 'decided_by' => $owner->id, 'decided_at' => now(), 'decision_reason' => $reason]);
            $this->event($r, $owner, 'owner', $decision, ['reason' => $reason]);
            $this->notifyRequester($r, 'refund.'.$decision);
            $this->audit->record($owner, 'refund.'.$decision, 'refund_request', $r->id, ['reason' => $reason]);

            return $r;
        });
    }

    public function withdraw(RefundRequest $refund, User $cashier, string $reason): RefundRequest
    {
        return DB::transaction(function () use ($refund, $cashier, $reason) {
            $r = RefundRequest::query()->whereKey($refund->id)->lockForUpdate()->firstOrFail();
            if ($r->status !== 'requested' || $r->requested_by !== $cashier->id) {
                throw new DomainError('INVALID_REFUND_STATE', 'Hanya Cashier pemohon yang dapat menarik pengajuan sebelum diputuskan.');
            }$r->update(['status' => 'withdrawn', 'withdrawn_at' => now(), 'withdrawn_by' => $cashier->id, 'withdraw_reason' => $reason]);
            $this->event($r, $cashier, 'cashier', 'withdrawn', ['reason' => $reason]);
            $this->notifications->sendRole('owner', 'refund.withdrawn', ['refund_request_id' => $r->id]);
            $this->audit->record($cashier, 'refund.withdrawn', 'refund_request', $r->id, ['reason' => $reason]);

            return $r;
        });
    }

    public function revoke(RefundRequest $refund, User $owner, string $reason): RefundRequest
    {
        return DB::transaction(function () use ($refund, $owner, $reason) {
            $r = RefundRequest::query()->whereKey($refund->id)->lockForUpdate()->firstOrFail();
            if ($r->status !== 'approved') {
                throw new DomainError('INVALID_REFUND_STATE', 'Hanya refund yang belum dieksekusi dapat dicabut.');
            }$r->update(['status' => 'approval_revoked', 'approval_revoked_at' => now(), 'approval_revoked_by' => $owner->id, 'approval_revoke_reason' => $reason]);
            $this->event($r, $owner, 'owner', 'approval_revoked', ['reason' => $reason]);
            $this->notifyRequester($r, 'refund.approval_revoked');
            $this->audit->record($owner, 'refund.approval_revoked', 'refund_request', $r->id, ['reason' => $reason]);

            return $r;
        });
    }

    public function execute(RefundRequest $refund, User $cashier, array $data): RefundRequest
    {
        return DB::transaction(function () use ($refund, $cashier, $data) {
            $paymentId = RefundRequest::whereKey($refund->id)->value('payment_id');
            $orderId = Payment::whereKey($paymentId)->value('order_id');
            Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($paymentId)->lockForUpdate()->firstOrFail();
            $r = RefundRequest::query()->whereKey($refund->id)->lockForUpdate()->with('payment')->firstOrFail();
            if ($r->status !== 'approved' || $r->execution) {
                throw new DomainError('INVALID_REFUND_STATE', 'Refund tidak dapat dieksekusi.');
            }$reserved = (int) RefundRequest::query()->where('payment_id', $r->payment_id)->whereIn('status', ['completed', 'approved'])->where('id', '<>', $r->id)->sum('amount');
            if ($reserved + $r->amount > (int) $payment->amount) {
                throw new DomainError('REFUND_LIMIT_EXCEEDED', 'Nilai refund melampaui pembayaran.', 409);
            }$method = $data['method'];
            if ($method === 'cash' && ($data['confirm_cash'] ?? false) !== true && ($data['confirm_cash'] ?? null) !== '1') {
                throw new DomainError('VALIDATION_FAILED', 'Cashier wajib mengonfirmasi pengembalian tunai.', 422);
            }if ($method === 'bank_transfer' && (! $data['reference_number'] || ! isset($data['proof']))) {
                throw new DomainError('VALIDATION_FAILED', 'Bukti dan nomor referensi transfer wajib.', 422);
            }$proof = isset($data['proof']) ? $data['proof']->store('refund-proofs', 'private') : null;
            RefundExecution::create(['refund_request_id' => $r->id, 'cashier_id' => $cashier->id, 'method' => $method, 'amount' => $r->amount, 'reference_number' => $data['reference_number'] ?? null, 'proof_path' => $proof, 'executed_at' => now()]);
            $r->update(['status' => 'completed', 'completed_at' => now()]);
            $total = (int) RefundRequest::query()->where('payment_id', $r->payment_id)->where('status', 'completed')->sum('amount');
            if ($total >= (int) $payment->amount) {
                $payment->update(['status' => 'refunded']);
            }$details = ['method' => $method, 'amount' => $r->amount, 'reference_number' => $data['reference_number'] ?? null, 'proof_uploaded' => (bool) $proof];
            $this->event($r, $cashier, 'cashier', 'completed', $details);
            $this->notifications->sendRole('owner', 'refund.completed', ['refund_request_id' => $r->id]);
            $this->notifyRequester($r, 'refund.completed');
            $this->audit->record($cashier, 'refund.completed', 'refund_request', $r->id, $details);

            return $r->load('execution');
        });
    }

    public function recordConflict(RefundRequest $r, User $user, array $data): RefundConflictNote
    {
        if ($r->status !== 'conflict_of_interest') {
            throw new DomainError('INVALID_REFUND_STATE', 'Dokumentasi hanya berlaku untuk konflik kepentingan.');
        }if (! in_array($user->role->value, ['manager', 'owner'], true)) {
            throw DomainError::forbidden();
        }$isOwner = $user->role->value === 'owner';
        $note = RefundConflictNote::create(['refund_request_id' => $r->id, 'recorded_by' => $user->id, 'handled_by' => $data['handled_by'], 'resolved_on' => $data['resolved_on'], 'outcome' => $data['outcome'], 'reason' => $data['reason'], 'amount' => $data['amount'], 'proof_path' => isset($data['proof']) ? $data['proof']->store('refund-conflicts', 'private') : null, 'verified_at' => $isOwner ? now() : null, 'verified_by' => $isOwner ? $user->id : null]);
        $this->event($r, $user, $user->role->value, 'conflict_note_recorded', ['note_id' => $note->id]);
        if (! $isOwner) {
            $this->notifications->sendRole('owner', 'refund.conflict_note_pending', ['refund_request_id' => $r->id, 'note_id' => $note->id]);
        }$this->audit->record($user, 'refund.conflict_note_recorded', 'refund_request', $r->id, ['note_id' => $note->id]);

        return $note;
    }

    public function verifyConflict(RefundConflictNote $note, User $owner): RefundConflictNote
    {
        if ($owner->role->value !== 'owner') {
            throw DomainError::forbidden();
        }if ($note->verified_at || $note->recorded_by === $owner->id) {
            throw new DomainError('INVALID_REFUND_STATE', 'Dokumentasi tidak dapat diverifikasi.');
        }$note->update(['verified_by' => $owner->id, 'verified_at' => now()]);
        $this->audit->record($owner, 'refund.conflict_note_verified', 'refund_request', $note->refund_request_id, ['note_id' => $note->id]);

        return $note;
    }

    private function event(RefundRequest $r, User $u, string $type, string $event, array $details): void
    {
        RefundEvent::create(['refund_request_id' => $r->id, 'actor_id' => $u->id, 'actor_type' => $type, 'event' => $event, 'details' => $details]);
    }

    private function notifyRequester(RefundRequest $r, string $type): void
    {
        $u = $r->requested_by ? User::find($r->requested_by) : null;
        if ($u && $u->role->value === 'kasir') {
            $this->notifications->send($u, $type, ['refund_request_id' => $r->id]);

            return;
        } $this->notifications->sendRole('kasir', $type, ['refund_request_id' => $r->id]);
    }
}
