<?php

namespace App\Enums;

/** Status pembelian TERPISAH dari penerimaan barang: stok hanya bertambah saat penerimaan dikonfirmasi. */
enum PurchaseStatus: string
{
    case Draft = 'draft';
    case Dipesan = 'dipesan';
    case DiterimaSebagian = 'diterima_sebagian';
    case DiterimaLengkap = 'diterima_lengkap';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Dipesan => 'Dipesan',
            self::DiterimaSebagian => 'Diterima Sebagian',
            self::DiterimaLengkap => 'Diterima Lengkap',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** @return list<self> */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Draft => [self::Dipesan, self::Dibatalkan],
            self::Dipesan => [self::DiterimaSebagian, self::DiterimaLengkap, self::Dibatalkan],
            self::DiterimaSebagian => [self::DiterimaSebagian, self::DiterimaLengkap],
            self::DiterimaLengkap, self::Dibatalkan => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }

    public function canReceive(): bool
    {
        return in_array($this, [self::Dipesan, self::DiterimaSebagian], true);
    }
}
