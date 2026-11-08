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
        if ($data['payment']) {
            $data['payment'] = ['method' => $data['payment']['method'], 'status' => $data['payment']['status'], 'amount' => $data['payment']['amount']];
        }
        $data['tickets'] = array_map(fn ($t) => ['station' => $t['station'], 'status' => $t['status']], $data['tickets']);

        return $data;
    }
}
