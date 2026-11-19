<?php

namespace App\Enums;

/** Role staf; station pada akun barista_kitchen membatasi izin antrean efektif. */
enum UserRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Kasir = 'kasir';
    case BaristaKitchen = 'barista_kitchen';
    // Retained only while populated legacy databases are being upgraded.
    case Barista = 'barista';
    case Kitchen = 'kitchen';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Manager => 'Manager',
            self::Kasir => 'Kasir',
            self::BaristaKitchen => 'Barista & Kitchen',
            self::Barista => 'Barista (legacy)',
            self::Kitchen => 'Kitchen (legacy)',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return [self::Owner->value, self::Manager->value, self::Kasir->value, self::BaristaKitchen->value];
    }
}
