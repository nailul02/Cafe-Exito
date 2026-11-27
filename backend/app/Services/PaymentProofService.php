<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentProofService
{
    public function __construct(private readonly AuditService $audit) {}

    public function upload(Order $order, UploadedFile $file): PaymentProof
    {
        if ($order->order_source !== 'qr_customer' || in_array($order->status->value, ['selesai', 'dibatalkan'], true)) {
            throw ValidationException::withMessages(['proof' => 'Pesanan ini tidak lagi menerima bukti pembayaran.']);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $image = @getimagesize($file->getRealPath());
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || ! $image || $image[0] < 1 || $image[1] < 1) {
            throw ValidationException::withMessages(['proof' => 'File harus berupa gambar JPEG, PNG, atau WEBP yang valid.']);
        }
        if ($file->getSize() > 3 * 1024 * 1024) {
            throw ValidationException::withMessages(['proof' => 'Ukuran file maksimal 3 MB.']);
        }
        $hash = hash_file('sha256', $file->getRealPath());
        if (PaymentProof::query()->where('sha256', $hash)->exists()) {
            throw ValidationException::withMessages(['proof' => 'File bukti identik sudah pernah diunggah.']);
        }

        $storedPath = null;
        try {
            return DB::transaction(function () use ($order, $file, $mime, $hash, &$storedPath) {
                $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($order->order_source !== 'qr_customer' || in_array($order->status->value, ['selesai', 'dibatalkan'], true)) {
                    throw ValidationException::withMessages(['proof' => 'Pesanan tidak lagi menerima bukti pembayaran.']);
                }
                $payment = $order->payments()->where('status', PaymentStatus::Pending->value)->lockForUpdate()->first();
                if (! $payment) {
                    $last = $order->payments()->latest('id')->lockForUpdate()->first();
                    if (! $last || $last->status !== PaymentStatus::Rejected || $last->method !== PaymentMethod::Qris) {
                        throw ValidationException::withMessages(['proof' => 'Pembayaran tidak tersedia untuk upload bukti.']);
                    }
                    $payment = $order->payments()->create(['method' => PaymentMethod::Qris->value, 'status' => PaymentStatus::Pending->value, 'amount' => $last->amount]);
                    $allocations = DB::table('payment_order_details')->where('payment_id', $last->id)
                        ->pluck('amount', 'order_detail_id')->map(fn ($amount) => (int) $amount)->all();
                    PaymentAllocationService::attach($payment, $allocations);
                }
                if ($payment->method !== PaymentMethod::Qris) {
                    throw ValidationException::withMessages(['proof' => 'Upload bukti hanya tersedia untuk pembayaran QRIS QR Self-Order.']);
                }
                if ($payment->proofs()->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages(['proof' => 'Bukti sebelumnya masih menunggu verifikasi.']);
                }

                $path = $file->store('payment-proofs', ['disk' => 'local']);
                $storedPath = $path;
                $proof = $payment->proofs()->create([
                    'path' => $path, 'mime_type' => $mime, 'size_bytes' => $file->getSize(), 'sha256' => $hash,
                    'status' => 'pending', 'uploaded_at' => now(),
                ]);
                $this->audit->record(null, 'payment_proof.uploaded', 'payment_proof', $proof->id, ['payment_id' => $payment->id, 'order_id' => $order->id, 'sha256' => $hash]);
                return $proof;
            });
        } catch (QueryException $e) {
            if ($storedPath) Storage::disk('local')->delete($storedPath);
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages(['proof' => 'File bukti identik sudah pernah diunggah.']);
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($storedPath) Storage::disk('local')->delete($storedPath);
            throw $e;
        }
    }

    public function review(Payment $payment, User $cashier, bool $accept, ?string $reason = null): ?PaymentProof
    {
        return DB::transaction(function () use ($payment, $cashier, $accept, $reason) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $proof = $payment->proofs()->where('status', 'pending')->latest('id')->lockForUpdate()->first();
            if (! $proof) return null; // POS QRIS manual verification has no customer proof.
            $proof->update(['status' => $accept ? 'verified' : 'rejected', 'reject_reason' => $accept ? null : $reason, 'reviewed_by' => $cashier->id, 'reviewed_at' => now()]);
            $this->audit->record($cashier, $accept ? 'payment_proof.verified' : 'payment_proof.rejected', 'payment_proof', $proof->id, ['payment_id' => $payment->id, 'reason' => $accept ? null : $reason]);
            return $proof;
        });
    }
}
