<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

enum DiscountType: string
{
    use BaseEnum;

    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return __("finance::finance.discount_types.{$this->value}");
    }
}
