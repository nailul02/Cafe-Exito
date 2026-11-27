<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/** Harga, total, dan status TIDAK diterima dari klien. */
class CreateOrderRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'table_number'      => ['nullable', 'string', 'regex:/^(?:[1-9]|1[0-5])$/'],
            'service_type'      => ['sometimes', Rule::in(['dine_in', 'takeaway'])],
            'customer_name'     => ['prohibited'],
            'payment_method'    => ['required', Rule::in(['tunai', 'qris'])],
            'client_request_id' => ['nullable', 'uuid'],
            'items'             => ['required', 'array', 'min:1', 'max:'.config('cafe.cart.max_lines', 30)],
            'items.*.menu_id'   => ['required', 'integer'],
            'items.*.qty'       => ['required', 'integer', 'min:1', 'max:'.config('cafe.cart.max_qty', 99)],
        ];
    }
}
