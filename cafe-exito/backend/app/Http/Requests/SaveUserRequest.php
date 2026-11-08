<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;

/** Role "owner" tidak dapat dibuat/diberikan lewat API (hanya seeder). */
class SaveUserRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge(['username' => strtolower((string) $this->input('username'))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('user');
        $creating = $this->isMethod('POST');

        return [
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:100'],
            'username' => [$creating ? 'required' : 'sometimes', 'string', 'max:50', 'regex:/^[a-z0-9._]+$/', Rule::unique('users', 'username')->ignore($id)],
            'email' => [$creating ? 'required' : 'sometimes', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id)],
            'role' => [$creating ? 'required' : 'sometimes', Rule::in(array_diff(UserRole::values(), ['owner']))],
            'password' => [$creating ? 'required' : 'prohibited', 'string', 'min:8', 'max:100'],
        ];
    }
}
