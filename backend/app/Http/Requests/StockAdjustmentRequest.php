<?php

namespace App\Http\Requests;

class StockAdjustmentRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'quantity_change' => ['required', 'numeric', 'not_in:0', 'between:-9999999,9999999'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }
}
