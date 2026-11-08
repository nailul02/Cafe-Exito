<?php

namespace App\Http\Requests;

class CancelOrderRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:255'], 'restore_stock' => ['sometimes', 'boolean']];
    }
}
