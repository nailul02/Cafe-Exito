<?php

namespace App\Models;

use App\Enums\DisplayGroup;
use App\Enums\Station;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    protected $fillable = ['name', 'display_group', 'station', 'sort_order'];

    protected function casts(): array
    {
        return ['display_group' => DisplayGroup::class, 'station' => Station::class];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'category_id');
    }
}
