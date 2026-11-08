<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SaveIngredientRequest extends ApiRequest
{
    public function rules(): array
    {
        $creating = $this->isMethod('POST');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('ingredients', 'name')->whereNull('deleted_at')->ignore($this->route('ingredient'))],
            'category' => ['nullable', 'string', 'max:60'],
            'unit' => ['required', 'string', 'max:20'],
            'min_stock' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            // Stok awal hanya saat membuat; perubahan stok selanjutnya lewat penerimaan/koreksi (satu pintu StockService).
            'stock' => [$creating ? 'nullable' : 'prohibited', 'numeric', 'min:0', 'max:9999999999'],
        ];
    }
}
