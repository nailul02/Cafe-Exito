<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PenerimaanPembelian = 'penerimaan_pembelian';
    case PemakaianPesanan = 'pemakaian_pesanan';
    case Koreksi = 'koreksi';
    case PembalikanPemakaian = 'pembalikan_pemakaian';

    public function label(): string
    {
        return match ($this) {
            self::PenerimaanPembelian => 'Penerimaan Pembelian',
            self::PemakaianPesanan => 'Pemakaian Pesanan',
            self::Koreksi => 'Koreksi',
            self::PembalikanPemakaian => 'Pembalikan Pemakaian',
        };
    }
}
