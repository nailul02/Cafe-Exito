<?php

namespace App\Http\Requests;

class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['login' => ['required', 'string', 'max:150'], 'password' => ['required', 'string', 'max:200']];
    }
}
