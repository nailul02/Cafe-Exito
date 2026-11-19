<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class VerifyPaymentRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['method' => ['nullable', Rule::in(['tunai', 'qris'])], 'amount_received' => ['nullable', 'integer', 'min:0']];
    }
}
