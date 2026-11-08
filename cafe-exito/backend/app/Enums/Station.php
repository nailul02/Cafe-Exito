<?php

namespace App\Enums;

/** Stasiun penyiapan pesanan. CONFIRMED: Barista dan Kitchen terpisah. */
enum Station: string
{
    case Barista = 'barista';
    case Kitchen = 'kitchen';

    public function label(): string
    {
        return $this === self::Barista ? 'Barista' : 'Kitchen';
    }

    /** Permission untuk menjalankan tiket stasiun ini. */
    public function permission(): string
    {
        return 'queue.'.$this->value;
    }
}
