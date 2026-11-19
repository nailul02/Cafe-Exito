<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** Versi pelanggan: tanpa ID internal, nama kasir, atau nomor transaksi. */
class CustomerOrderResource extends OrderResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        unset($data['id']);
        unset($data['payment_history']);
        if ($data['payment']) {
            $data['payment'] = ['method' => $data['payment']['method'], 'status' => $data['payment']['status'], 'amount' => $data['payment']['amount'], 'reject_reason' => $data['payment']['reject_reason'], 'proof' => $data['payment']['proof'] ? ['status' => $data['payment']['proof']['status'], 'reject_reason' => $data['payment']['proof']['reject_reason'], 'uploaded_at' => $data['payment']['proof']['uploaded_at']] : null];
            $previous = $this->payments->flatMap(fn ($payment) => $payment->proofs)->where('status', 'rejected')->sortByDesc('id')->first();
            $data['payment']['previous_rejection'] = $previous ? ['reason' => $previous->reject_reason, 'uploaded_at' => $previous->uploaded_at?->toIso8601String()] : null;
        }
        $data['tickets'] = array_map(fn ($t) => ['station' => $t['station'], 'status' => $t['status']], $data['tickets']);

        return $data;
    }
}
