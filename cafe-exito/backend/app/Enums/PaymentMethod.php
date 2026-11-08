<?php

namespace App\Enums;

/** CONFIRMED: hanya tunai dan QRIS statis dengan verifikasi manual; tanpa payment gateway. */
enum PaymentMethod: string
{
    case Tunai = 'tunai';
    case Qris = 'qris';

    public function label(): string
    {
        return $this === self::Tunai ? 'Tunai' : 'QRIS';
    }
}
