<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SaveMenuRequest extends ApiRequest
{
    public function rules(): array
    {
        $id = $this->route('menu');

        return [
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:30', Rule::unique('menus', 'sku')->whereNull('deleted_at')->ignore($id)],
            'category_id' => ['required', 'integer', 'exists:menu_categories,id'],
            'price' => ['required', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'station' => ['nullable', Rule::in(['barista', 'kitchen'])],
            'image' => ['nullable', 'image', 'mimes:'.implode(',', config('cafe.upload.image_mimes')), 'max:'.config('cafe.upload.image_max_kb')],
        ];
    }
}
