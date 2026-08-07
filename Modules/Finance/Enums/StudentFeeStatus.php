<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

enum StudentFeeStatus: string
{
    use BaseEnum;

    case Pending = 'pending';
    case Invoiced = 'invoiced';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("finance::finance.student_fee_statuses.{$this->value}");
    }
}
