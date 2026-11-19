<?php

namespace App\Enums;

/** Tiga tab katalog pelanggan (Figma P-03). */
enum DisplayGroup: string
{
    case Minuman = 'minuman';
    case Makanan = 'makanan';
    case Cemilan = 'cemilan';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
