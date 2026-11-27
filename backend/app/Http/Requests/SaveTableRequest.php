<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SaveTableRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'table_number' => ['required', 'string', 'regex:/^(?:[1-9]|1[0-5])$/', Rule::unique('tables', 'table_number')->ignore($this->route('table'))],
            'status' => ['nullable', Rule::in(['active'])],
        ];
    }
}
