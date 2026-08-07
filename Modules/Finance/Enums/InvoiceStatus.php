<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

enum InvoiceStatus: string
{
    use BaseEnum;

    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Overdue = 'overdue';

    public function label(): string
    {
        return __("finance::finance.invoice_statuses.{$this->value}");
    }

    public function isSettled(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled], true);
    }
}
