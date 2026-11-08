<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Table */
class TableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_number' => $this->table_number,
            'qr_token' => $this->qr_token,
            'status' => $this->status,
            'qr_url' => $this->qrUrl(),
        ];
    }
}
