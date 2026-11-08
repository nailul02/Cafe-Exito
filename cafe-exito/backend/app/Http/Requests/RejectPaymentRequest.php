<?php

namespace App\Http\Requests;

class RejectPaymentRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['reason' => ['nullable', 'string', 'max:255']];
    }
}
