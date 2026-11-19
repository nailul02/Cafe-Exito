<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/** Payload POS kasir; harga dan total selalu dihitung ulang di backend. */
class CreatePosOrderRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'client_request_id' => ['required', 'uuid'],
            'service_type' => ['required', Rule::in(['dine_in', 'takeaway'])],
            'table_number' => [
                'nullable', 'string', 'max:10',
                Rule::exists('tables', 'table_number')->where('status', 'active'),
            ],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', Rule::in(['tunai', 'qris'])],
            // Uang diterima hanya dicatat saat kasir mengonfirmasi pelunasan.
            'amount_received' => ['prohibited'],
            'items' => ['required', 'array', 'min:1', 'max:'.config('cafe.cart.max_lines', 30)],
            'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.config('cafe.cart.max_qty', 99)],
        ];
    }
}
