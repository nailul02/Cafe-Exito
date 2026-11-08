<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditService;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    /** Login dengan email ATAU username (K-T1 PENDING: Figma memakai email, laporan memakai username). */
    public function login(LoginRequest $request)
    {
        $login = mb_strtolower(trim($request->validated('login')));
        $user = User::query()->where('email', $login)->orWhere('username', $login)->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            $this->audit->record($user, 'auth.login_failed', 'user', $user?->id, ['login' => $login]);
            throw ValidationException::withMessages(['login' => ['Email/username atau kata sandi salah.']]);
        }
        if (! $user->is_active) {
            throw new AuthorizationException('Akun dinonaktifkan.');
        }

        $minutes = config('sanctum.expiration');
        $token = $user->createToken('api', ['*'], $minutes ? now()->addMinutes((int) $minutes) : null);
        $user->forceFill(['last_login_at' => now()])->save();

        return ApiResponse::success([
            'token' => $token->plainTextToken,
            'expires_at' => $minutes ? now()->addMinutes((int) $minutes)->toIso8601String() : null,
            'user' => UserResource::make($user)->resolve($request),
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        return ApiResponse::success(UserResource::make($request->user()));
    }

    /** Profil sendiri: nama dan email. Foto profil: PENDING. */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return ApiResponse::success(UserResource::make($user->refresh()), 'Profil diperbarui');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['Kata sandi saat ini salah.']]);
        }
        $user->update(['password' => $request->validated('password')]);
        // Cabut token lain; token saat ini tetap dipakai.
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        $this->audit->record($user, 'auth.password_changed', 'user', $user->id);

        return ApiResponse::success(null, 'Kata sandi diperbarui');
    }
}
