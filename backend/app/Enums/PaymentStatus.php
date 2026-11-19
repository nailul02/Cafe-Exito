<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }

    /** @return list<self> */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Pending => [self::Verified, self::Rejected, self::Cancelled],
            self::Verified => [self::Refunded],
            self::Rejected, self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }
}
