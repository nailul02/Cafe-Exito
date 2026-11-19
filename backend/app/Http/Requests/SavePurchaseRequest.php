<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SavePurchaseRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')->where('is_active', true)],
            'note' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1', 'max:100'],
            'details.*.ingredient_id' => ['required', 'integer', 'distinct', 'exists:ingredients,id'],
            'details.*.qty_ordered' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'details.*.unit_price' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
