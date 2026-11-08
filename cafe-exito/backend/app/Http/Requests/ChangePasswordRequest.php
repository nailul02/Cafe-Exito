<?php

namespace App\Http\Requests;

class ChangePasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:8', 'max:100', 'different:current_password']];
    }
}
