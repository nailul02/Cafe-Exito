<?php

namespace App\Http\Requests;

class RejectPaymentRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:1', 'max:255']];
    }
}
