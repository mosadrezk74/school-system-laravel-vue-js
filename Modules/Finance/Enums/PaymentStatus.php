<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

enum PaymentStatus: string
{
    use BaseEnum;

    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function label(): string
    {
        return __("finance::finance.payment_statuses.{$this->value}");
    }

    /**
     * Whether money is actually in the school's hands. Only these states count
     * toward an invoice's paid total, so a parent's pending request never
     * reduces the balance they still owe.
     */
    public function isSettled(): bool
    {
        return in_array($this, [self::Paid, self::Refunded, self::PartiallyRefunded], true);
    }

    public function isAwaitingConfirmation(): bool
    {
        return $this === self::Pending;
    }

    public function isRefundable(): bool
    {
        return in_array($this, [self::Paid, self::PartiallyRefunded], true);
    }

    /**
     * @return array<int, string>
     */
    public static function settledValues(): array
    {
        return array_map(
            fn (self $status): string => $status->value,
            array_filter(self::cases(), fn (self $status): bool => $status->isSettled()),
        );
    }
}
