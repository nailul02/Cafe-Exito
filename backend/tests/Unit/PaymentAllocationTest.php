<?php

namespace Tests\Unit;

use App\Services\PaymentAllocationService;
use PHPUnit\Framework\TestCase;

class PaymentAllocationTest extends TestCase
{
    public function test_payment_allocation_is_split_across_cancelled_unit_ranges_without_losing_minor_units(): void
    {
        $parts = [
            PaymentAllocationService::amountForRange(10001, 3, 1, 1),
            PaymentAllocationService::amountForRange(10001, 3, 2, 2),
            PaymentAllocationService::amountForRange(10001, 3, 3, 3),
        ];

        $this->assertSame([3333, 3334, 3334], $parts);
        $this->assertSame(10001, array_sum($parts));
    }

    public function test_allocation_range_is_capped_to_the_original_line_quantity(): void
    {
        $this->assertSame(0, PaymentAllocationService::amountForRange(900, 3, 4, 5));
        $this->assertSame(600, PaymentAllocationService::amountForRange(900, 3, 2, 99));
    }
}
