<?php

namespace App\Http\Requests;

class AttendanceCorrectionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'check_in_at' => ['sometimes', 'nullable', 'date'],
            'check_out_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:check_in_at'],
            'status' => ['sometimes', 'in:present,absent,sick,leave'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
