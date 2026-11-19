<?php

namespace App\Http\Requests;

class ValidateCartRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:'.config('cafe.cart.max_lines', 30)],
            'items.*.menu_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.config('cafe.cart.max_qty', 99)],
        ];
    }
}
