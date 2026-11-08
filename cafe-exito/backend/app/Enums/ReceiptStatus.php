<?php

namespace App\Enums;

enum ReceiptStatus: string
{
    case Draft = 'draft';
    case Dikonfirmasi = 'dikonfirmasi';

    public function label(): string
    {
        return $this === self::Draft ? 'Draft' : 'Dikonfirmasi';
    }
}
