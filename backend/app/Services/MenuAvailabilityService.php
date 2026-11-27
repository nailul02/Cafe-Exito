<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/** Ketersediaan menu hanya mengikuti saklar operasional manual; stok tidak lagi digunakan. */
class MenuAvailabilityService
{
    /** @param Collection<int, Menu> $menus */
    public function load(Collection $menus): Collection
    {
        return $menus->load('category');
    }

    /** @return array{orderable: bool, reason: ?string} */
    public function evaluate(Menu $menu): array
    {
        if (! $menu->is_available) {
            return ['orderable' => false, 'reason' => 'Habis'];
        }
        return ['orderable' => true, 'reason' => null];
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->role->value === 'barista_kitchen' || in_array($user->role->value, ['barista', 'kitchen'], true)) {
            $station = $user->station?->value;
            if ($station === null) return $query->whereRaw('1 = 0');
            return $query->whereHas('category', fn (Builder $q) => $q->where('station', $station));
        }
        return $query;
    }

    public function mayChange(User $user, Menu $menu): bool
    {
        if ($user->role->value === 'owner') return true;
        if ($user->role->value !== 'barista_kitchen' && ! in_array($user->role->value, ['barista', 'kitchen'], true)) return false;
        return $user->station !== null && $menu->category?->station === $user->station;
    }
}
