<?php

namespace App\Enums;

/** Level stok terhadap stok minimum (K-T5, ASUMSI): habis=0; kritis<=50%; menipis<=100%; aman>100%. */
enum StockLevel: string
{
    case Habis = 'habis';
    case Kritis = 'kritis';
    case Menipis = 'menipis';
    case Aman = 'aman';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public static function calculate(float|int|string $stock, float|int|string $min, float $criticalRatio = 0.5): self
    {
        $stock = (float) $stock;
        $min = (float) $min;
        if ($stock <= 0) {
            return self::Habis;
        }
        if ($min <= 0) {
            return self::Aman;
        }
        $ratio = $stock / $min;

        return match (true) {
            $ratio <= $criticalRatio => self::Kritis,
            $ratio <= 1 => self::Menipis,
            default => self::Aman,
        };
    }

    public function isLow(): bool
    {
        return $this !== self::Aman;
    }
}
