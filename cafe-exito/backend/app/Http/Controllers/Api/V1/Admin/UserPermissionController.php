<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavePermissionsRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\PermissionService;
use App\Support\ApiResponse;

/** Izin khusus per akun (mis. barista membantu Kitchen). Hanya izin pada daftar `grantable`. */
class UserPermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function show(User $user)
    {
        return ApiResponse::success([
            'defaults' => $this->permissions->roleDefaults($user->role->value),
            'granted' => $this->permissions->granted($user),
            'grantable' => $this->permissions->grantable(),
        ]);
    }

    public function update(SavePermissionsRequest $request, User $user)
    {
        $this->permissions->sync($user, $request->validated('permissions'), $request->user());

        return ApiResponse::success(UserResource::make($user->refresh()), 'Izin khusus disimpan');
    }
}
