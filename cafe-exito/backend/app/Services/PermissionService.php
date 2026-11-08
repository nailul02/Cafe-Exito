<?php

namespace App\Services;

use App\Exceptions\DomainError;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;

/** Izin efektif = bawaan role (config/permissions.php) + izin khusus per akun (user_permissions). */
class PermissionService
{
    /** @var array<int, list<string>> */
    private array $memo = [];

    public function __construct(private readonly AuditService $audit) {}

    /** @return list<string> */
    public function roleDefaults(string $role): array
    {
        return config("permissions.roles.$role", []);
    }

    /** @return list<string> */
    public function grantable(): array
    {
        return config('permissions.grantable', []);
    }

    /** @return list<string> */
    public function granted(User $user): array
    {
        return $user->extraPermissions()->pluck('permission')->all();
    }

    /** @return list<string> */
    public function effective(User $user): array
    {
        return $this->memo[$user->id] ??= array_values(array_unique(array_merge(
            $this->roleDefaults($user->role->value),
            $this->granted($user),
        )));
    }

    public function has(?User $user, string $permission): bool
    {
        return $user !== null && in_array($permission, $this->effective($user), true);
    }

    /**
     * Mengganti seluruh izin khusus akun. Hanya izin pada daftar `grantable` yang boleh diberikan,
     * dan akun Owner tidak dapat diubah.
     *
     * @param  list<string>  $permissions
     */
    public function sync(User $target, array $permissions, User $actor): void
    {
        if ($target->role->value === 'owner') {
            throw DomainError::forbidden('Izin akun Owner tidak dapat diubah.');
        }
        $invalid = array_diff($permissions, $this->grantable());
        if ($invalid) {
            throw new DomainError('VALIDATION_FAILED', 'Ada izin yang tidak dapat diberikan: '.implode(', ', $invalid).'.', 422);
        }
        $before = $this->granted($target);
        DB::transaction(function () use ($target, $permissions, $actor) {
            $target->extraPermissions()->delete();
            foreach (array_unique($permissions) as $permission) {
                UserPermission::create(['user_id' => $target->id, 'permission' => $permission, 'granted_by' => $actor->id]);
            }
        });
        unset($this->memo[$target->id]);
        $this->audit->record($actor, 'permissions.changed', 'user', $target->id, ['before' => $before, 'after' => array_values(array_unique($permissions))]);
    }
}
