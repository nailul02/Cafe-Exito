<?php

namespace App\Http\Resources;

use App\Services\PermissionService;
use App\Support\Rp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => Rp::enum($this->role),
            'is_active' => $this->is_active,
            'permissions' => app(PermissionService::class)->effective($this->resource),
        ];
    }
}
