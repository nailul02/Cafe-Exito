<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SalesReportRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'granularity' => ['nullable', Rule::in(['hour', 'day'])],
        ];
    }
}
