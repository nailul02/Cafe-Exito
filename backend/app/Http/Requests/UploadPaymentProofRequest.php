<?php

namespace App\Http\Requests;

class UploadPaymentProofRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072']];
    }
}
