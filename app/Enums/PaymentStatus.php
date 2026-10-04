<?php

namespace App\Enums;

/**
 * Payment lifecycle, deliberately kept separate from OrderStatus: an order can
 * be pending while its payment is paid, expired, or refunded.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Dibayar',
            self::Failed => 'Gagal',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dana Dikembalikan',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Failed, self::Expired, self::Cancelled],
            // An unsettled payment may be retried.
            self::Failed, self::Expired, self::Cancelled => [self::Pending],
            self::Paid => [self::Refunded],
            self::Refunded => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    /**
     * A settled payment has reached money-moving state and is no longer
     * awaiting a customer action.
     */
    public function isSettled(): bool
    {
        return $this === self::Paid || $this === self::Refunded;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
