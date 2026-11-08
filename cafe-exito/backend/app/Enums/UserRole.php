<?php

namespace App\Enums;

/** Lima role akun staf. "Pelanggan" bukan akun (akses lewat QR + token). CONFIRMED: enam role. */
enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Kasir = 'kasir';
    case Barista = 'barista';
    case Kitchen = 'kitchen';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Manager',
            self::Kasir => 'Kasir',
            self::Barista => 'Barista',
            self::Kitchen => 'Kitchen',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $r) => $r->value, self::cases());
    }
}
