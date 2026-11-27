<?php

namespace App\Http\Requests;

class OpenTableSessionRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['qr_token' => ['required', 'string', 'max:80'], 'customer_name' => ['sometimes', 'nullable', 'string', 'max:100']];
    }
}
