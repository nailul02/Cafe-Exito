<?php

namespace App\Http\Requests;

class SaveRecipeRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'items' => ['present', 'array'],
            'items.*.ingredient_id' => ['required', 'integer', 'distinct', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:9999999'],
        ];
    }
}
