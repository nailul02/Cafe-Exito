<?php

namespace App\Http\Requests;

class RejectPaymentRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:255']];
    }
}
