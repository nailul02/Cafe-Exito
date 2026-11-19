<?php

namespace App\Enums;

/** Tiga status tiket (K-B12 PENDING: "Siap Antar" belum ditambahkan). */
enum TicketStatus: string
{
    case Antre = 'antre';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Antre => 'Antre',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** @return list<self> */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Antre => [self::Diproses, self::Dibatalkan],
            self::Diproses => [self::Selesai, self::Dibatalkan],
            self::Selesai, self::Dibatalkan => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Antre, self::Diproses], true);
    }
}
