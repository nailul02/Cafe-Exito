<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';
    case Dibayar = 'dibayar';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::Dibayar => 'Dibayar',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** @return list<self> */
    public function allowedNext(): array
    {
        return match ($this) {
            self::MenungguPembayaran => [self::Dibayar, self::Dibatalkan],
            self::Dibayar => [self::Diproses, self::Selesai, self::Dibatalkan],
            self::Diproses => [self::Selesai, self::Dibatalkan],
            self::Selesai, self::Dibatalkan => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }

    /** Pesanan yang sudah dibayar dan dihitung sebagai penjualan. */
    public function isSale(): bool
    {
        return in_array($this, [self::Dibayar, self::Diproses, self::Selesai], true);
    }
}
