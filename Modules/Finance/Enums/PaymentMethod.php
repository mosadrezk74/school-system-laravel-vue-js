<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

enum PaymentMethod: string
{
    use BaseEnum;

    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Online = 'online';
    case Cheque = 'cheque';

    public function label(): string
    {
        return __("finance::finance.payment_methods.{$this->value}");
    }
}
