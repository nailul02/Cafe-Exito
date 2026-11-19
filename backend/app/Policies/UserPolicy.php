<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** Hanya Owner dapat mengelola akun staf; akun Owner dan akun sendiri selalu dilindungi. */
class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        return $actor->role === UserRole::Owner
            && $target->role !== UserRole::Owner
            && $actor->id !== $target->id;
    }
}
