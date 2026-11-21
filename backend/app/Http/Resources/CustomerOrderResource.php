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
            $data['payment'] = array_intersect_key($data['payment'], array_flip(['method', 'status', 'amount', 'proof']));
        }
        $data['tickets'] = array_map(fn ($t) => ['station' => $t['station'], 'status' => $t['status']], $data['tickets']);

        return $data;
    }
}
