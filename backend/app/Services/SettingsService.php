<?php

namespace App\Services;

use App\Models\AppSetting;

/** Pengaturan kecil (K-T8 ASUMSI): nama/alamat kafe untuk struk, gambar QRIS statis. */
class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $row = AppSetting::query()->where('key', $key)->first();

        return $row ? ($row->value['v'] ?? $default) : $default;
    }

    public function set(string $key, mixed $value): void
    {
        AppSetting::query()->updateOrCreate(['key' => $key], ['value' => ['v' => $value]]);
    }
}
