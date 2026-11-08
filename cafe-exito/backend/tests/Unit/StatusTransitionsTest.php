<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Enums\TicketStatus;
use PHPUnit\Framework\TestCase;

/** Mesin status (tanpa database). Transisi di luar tabel = INVALID_STATE_TRANSITION di Service. */
class StatusTransitionsTest extends TestCase
{
    public function test_pesanan_alur_normal(): void
    {
        $this->assertTrue(OrderStatus::MenungguPembayaran->canTransitionTo(OrderStatus::Dibayar));
        $this->assertTrue(OrderStatus::Dibayar->canTransitionTo(OrderStatus::Diproses));
        $this->assertTrue(OrderStatus::Diproses->canTransitionTo(OrderStatus::Selesai));
    }

    public function test_pesanan_tidak_boleh_lompat_atau_mundur(): void
    {
        $this->assertFalse(OrderStatus::MenungguPembayaran->canTransitionTo(OrderStatus::Diproses));
        $this->assertFalse(OrderStatus::MenungguPembayaran->canTransitionTo(OrderStatus::Selesai));
        $this->assertFalse(OrderStatus::Diproses->canTransitionTo(OrderStatus::Dibayar));
    }

    public function test_status_akhir_tidak_punya_transisi(): void
    {
        foreach ([OrderStatus::Selesai, OrderStatus::Dibatalkan] as $s) {
            $this->assertSame([], $s->allowedNext());
        }
        foreach ([TicketStatus::Selesai, TicketStatus::Dibatalkan] as $s) {
            $this->assertSame([], $s->allowedNext());
        }
        foreach ([PaymentStatus::Rejected, PaymentStatus::Cancelled, PaymentStatus::Refunded] as $s) {
            $this->assertSame([], $s->allowedNext());
        }
        foreach ([PurchaseStatus::DiterimaLengkap, PurchaseStatus::Dibatalkan] as $s) {
            $this->assertSame([], $s->allowedNext());
        }
    }

    public function test_hanya_pesanan_terbayar_dihitung_penjualan(): void
    {
        $this->assertFalse(OrderStatus::MenungguPembayaran->isSale());
        $this->assertFalse(OrderStatus::Dibatalkan->isSale());
        foreach ([OrderStatus::Dibayar, OrderStatus::Diproses, OrderStatus::Selesai] as $s) {
            $this->assertTrue($s->isSale());
        }
    }

    public function test_tiket(): void
    {
        $this->assertTrue(TicketStatus::Antre->canTransitionTo(TicketStatus::Diproses));
        $this->assertFalse(TicketStatus::Antre->canTransitionTo(TicketStatus::Selesai), 'tiket tidak boleh selesai tanpa diproses (stok belum dikurangi)');
        $this->assertTrue(TicketStatus::Diproses->canTransitionTo(TicketStatus::Selesai));
        $this->assertTrue(TicketStatus::Antre->isOpen());
        $this->assertFalse(TicketStatus::Selesai->isOpen());
    }

    public function test_pembayaran_terverifikasi_hanya_bisa_refund(): void
    {
        $this->assertSame([PaymentStatus::Refunded], PaymentStatus::Verified->allowedNext());
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Verified));
        $this->assertFalse(PaymentStatus::Rejected->canTransitionTo(PaymentStatus::Verified));
    }

    public function test_pembelian_terpisah_dari_penerimaan(): void
    {
        $this->assertFalse(PurchaseStatus::Draft->canReceive(), 'draft belum boleh menerima barang');
        $this->assertTrue(PurchaseStatus::Dipesan->canReceive());
        $this->assertTrue(PurchaseStatus::DiterimaSebagian->canReceive());
        $this->assertFalse(PurchaseStatus::DiterimaLengkap->canReceive());
        $this->assertFalse(PurchaseStatus::Dibatalkan->canReceive());
        $this->assertTrue(PurchaseStatus::Draft->canTransitionTo(PurchaseStatus::Dipesan));
        $this->assertFalse(PurchaseStatus::Draft->canTransitionTo(PurchaseStatus::DiterimaLengkap));
    }
}
