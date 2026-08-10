<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * How a configured payment method behaves, as opposed to what it is called.
 * The school names its own methods ("InstaPay", "Mada"); the type is what
 * tells the portal whether to redirect, show bank details, or wait for a
 * cashier.
 */
enum PaymentMethodType: string
{
    use BaseEnum;

    case Online = 'online';
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';

    public function label(): string
    {
        return __("finance::finance.payment_method_types.{$this->value}");
    }

    /** Online methods send the parent to the configured URL after the request exists. */
    public function usesPaymentUrl(): bool
    {
        return $this === self::Online;
    }

    /** Bank transfers display the configured account details as instructions. */
    public function showsBankDetails(): bool
    {
        return $this === self::BankTransfer;
    }
}
