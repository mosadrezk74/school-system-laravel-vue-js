<?php

namespace Modules\Finance\Enums;

use Modules\Core\Traits\BaseEnum;

enum FeeFrequency: string
{
    use BaseEnum;

    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Semester = 'semester';
    case Annual = 'annual';

    public function label(): string
    {
        return __("finance::finance.frequencies.{$this->value}");
    }
}
