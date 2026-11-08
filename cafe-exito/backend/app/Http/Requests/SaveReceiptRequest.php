<?php

namespace App\Http\Requests;

class SaveReceiptRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
            'received_at' => ['nullable', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.purchase_detail_id' => ['required', 'integer', 'distinct'],
            'details.*.qty_received' => ['required', 'numeric', 'gt:0', 'max:9999999'],
        ];
    }
}
