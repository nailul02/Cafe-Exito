<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Manajemen akun staf. Akun Owner tidak dapat dibuat/diubah/dinonaktifkan di sini (UserPolicy). */
class UserController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index()
    {
        return ApiResponse::success(UserResource::collection(User::query()->with('extraPermissions')->orderBy('name')->get()));
    }

    public function store(SaveUserRequest $request)
    {
        $user = User::create($request->validated());
        $this->audit->record($request->user(), 'user.created', 'user', $user->id, ['role' => $user->role->value]);

        return ApiResponse::created(UserResource::make($user), 'Pengguna dibuat');
    }

    public function update(SaveUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);
        $user->update($request->validated());
        $this->audit->record($request->user(), 'user.updated', 'user', $user->id, $request->validated());

        return ApiResponse::success(UserResource::make($user->refresh()), 'Pengguna diperbarui');
    }

    /** Menonaktifkan akun mencabut seluruh tokennya. */
    public function status(Request $request, User $user)
    {
        Gate::authorize('update', $user);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $user->update($data);
        if (! $data['is_active']) {
            $user->tokens()->delete();
        }
        $this->audit->record($request->user(), $data['is_active'] ? 'user.activated' : 'user.deactivated', 'user', $user->id);

        return ApiResponse::success(UserResource::make($user), 'Status pengguna diperbarui');
    }

    public function resetPassword(Request $request, User $user)
    {
        Gate::authorize('update', $user);
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'max:100']]);
        $user->update(['password' => $data['password']]);
        $user->tokens()->delete();
        $this->audit->record($request->user(), 'user.password_reset', 'user', $user->id);

        return ApiResponse::success(null, 'Kata sandi diatur ulang');
    }
}
