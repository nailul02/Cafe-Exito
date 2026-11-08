<?php

namespace Tests\Unit;

use App\Enums\DisplayGroup;
use App\Enums\PaymentMethod;
use App\Enums\Station;
use App\Enums\UserRole;
use App\Exceptions\DomainError;
use PHPUnit\Framework\TestCase;

/** Kode galat + batasan keputusan proyek (metode bayar, stasiun, role). */
class DomainRulesTest extends TestCase
{
    public function test_hanya_tunai_dan_qris(): void
    {
        $this->assertSame(['tunai', 'qris'], array_map(fn ($c) => $c->value, PaymentMethod::cases()), 'EDC/transfer/gateway di luar keputusan');
    }

    public function test_stasiun_dan_role(): void
    {
        $this->assertSame(['barista', 'kitchen'], array_map(fn ($c) => $c->value, Station::cases()));
        $this->assertSame(['owner', 'admin', 'kasir', 'barista', 'kitchen'], UserRole::values());
        $this->assertSame('queue.kitchen', Station::Kitchen->permission());
        $this->assertSame(['minuman', 'makanan', 'cemilan'], array_map(fn ($c) => $c->value, [DisplayGroup::Minuman, DisplayGroup::Makanan, DisplayGroup::Cemilan]));
    }

    public function test_kode_dan_status_http_galat_domain(): void
    {
        $cases = [
            [DomainError::insufficientStock(['Kopi']), 'INSUFFICIENT_STOCK', 409],
            [DomainError::invalidTransition('Tiket', 'antre', 'selesai'), 'INVALID_STATE_TRANSITION', 409],
            [DomainError::menuUnavailable('Latte'), 'MENU_UNAVAILABLE', 409],
            [DomainError::tableInvalid(), 'TABLE_INVALID', 422],
            [DomainError::paymentAlreadyProcessed(), 'PAYMENT_ALREADY_PROCESSED', 409],
            [DomainError::amountTooLow(10000), 'AMOUNT_TOO_LOW', 422],
            [DomainError::orderNotCancellable(), 'ORDER_NOT_CANCELLABLE', 409],
            [DomainError::purchaseNotEditable(), 'PURCHASE_NOT_EDITABLE', 409],
            [DomainError::overReceipt(), 'OVER_RECEIPT', 409],
            [DomainError::receiptAlreadyConfirmed(), 'RECEIPT_ALREADY_CONFIRMED', 409],
            [DomainError::forbidden(), 'FORBIDDEN', 403],
        ];
        foreach ($cases as [$error, $code, $status]) {
            $this->assertSame($code, $error->errorCode);
            $this->assertSame($status, $error->status);
            $this->assertNotSame('', $error->getMessage());
        }
    }

    public function test_label_enum_tidak_kosong(): void
    {
        foreach ([...\App\Enums\OrderStatus::cases(), ...\App\Enums\TicketStatus::cases(), ...\App\Enums\PaymentStatus::cases(), ...\App\Enums\PurchaseStatus::cases(), ...\App\Enums\StockMovementType::cases(), ...\App\Enums\UserRole::cases()] as $case) {
            $this->assertNotSame('', $case->label(), get_class($case).'::'.$case->name);
        }
    }
}
