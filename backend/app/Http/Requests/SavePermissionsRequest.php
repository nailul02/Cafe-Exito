<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SavePermissionsRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['permissions' => ['present', 'array'], 'permissions.*' => ['string', Rule::in(config('permissions.grantable', []))]];
    }
}
