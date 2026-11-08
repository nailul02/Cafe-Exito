<?php

namespace Tests\Unit;

use App\Enums\StockLevel;
use PHPUnit\Framework\TestCase;

/** Tanpa database/framework: aturan murni level stok (K-T5). Contoh angka diambil dari desain Figma Kelola Stok. */
class StockLevelTest extends TestCase
{
    public function test_habis_saat_stok_nol_atau_kurang(): void
    {
        $this->assertSame(StockLevel::Habis, StockLevel::calculate(0, 2000));
        $this->assertSame(StockLevel::Habis, StockLevel::calculate(-1, 2000));
    }

    public function test_contoh_figma(): void
    {
        $this->assertSame(StockLevel::Kritis, StockLevel::calculate(1.2, 5.0));    // 24%
        $this->assertSame(StockLevel::Kritis, StockLevel::calculate(4.0, 12.0));   // 33%
        $this->assertSame(StockLevel::Menipis, StockLevel::calculate(120, 150));   // 80%
        $this->assertSame(StockLevel::Aman, StockLevel::calculate(3.8, 2.0));      // 190%
    }

    public function test_batas_kritis_dan_menipis(): void
    {
        $this->assertSame(StockLevel::Kritis, StockLevel::calculate(50, 100));     // tepat 50% = kritis
        $this->assertSame(StockLevel::Menipis, StockLevel::calculate(51, 100));
        $this->assertSame(StockLevel::Menipis, StockLevel::calculate(100, 100));   // tepat 100% = menipis
        $this->assertSame(StockLevel::Aman, StockLevel::calculate(101, 100));
    }

    public function test_tanpa_stok_minimum_dianggap_aman_bila_ada_stok(): void
    {
        $this->assertSame(StockLevel::Aman, StockLevel::calculate(5, 0));
    }

    public function test_menerima_string_desimal_dari_database(): void
    {
        $this->assertSame(StockLevel::Kritis, StockLevel::calculate('1800.000', '5000.000'));
    }

    public function test_isLow(): void
    {
        $this->assertFalse(StockLevel::Aman->isLow());
        $this->assertTrue(StockLevel::Menipis->isLow());
        $this->assertTrue(StockLevel::Habis->isLow());
    }
}
