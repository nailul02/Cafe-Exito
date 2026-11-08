<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** Admin/Manager tidak boleh mengubah, menonaktifkan, atau mengatur ulang akun Owner (cegah eskalasi hak). */
class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        return $target->role !== UserRole::Owner && $actor->id !== $target->id;
    }
}
