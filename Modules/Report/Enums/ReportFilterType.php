<?php

namespace Modules\Report\Enums;

use Modules\Core\Traits\BaseEnum;

enum ReportFilterType: string
{
    use BaseEnum;

    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Select = 'select';
    case Boolean = 'boolean';

    public function label(): string
    {
        return __("report::report.filter_types.{$this->value}");
    }
}
