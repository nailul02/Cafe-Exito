<?php

namespace App\Support;

/** Pembantu angka: uang = bilangan bulat rupiah (T4); jumlah bahan = string desimal 3 digit (T5). */
final class Rp
{
    public static function qty(float|int|string|null $value): ?string
    {
        return $value === null ? null : number_format((float) $value, 3, '.', '');
    }

    public static function enum(\BackedEnum $enum): array
    {
        return ['code' => $enum->value, 'label' => method_exists($enum, 'label') ? $enum->label() : $enum->value];
    }
}
