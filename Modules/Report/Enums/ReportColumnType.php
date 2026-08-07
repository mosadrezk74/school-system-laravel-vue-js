<?php

namespace Modules\Report\Enums;

use Modules\Core\Traits\BaseEnum;

enum ReportColumnType: string
{
    use BaseEnum;

    case Text = 'text';
    case Number = 'number';
    case Money = 'money';
    case Percentage = 'percentage';
    case Date = 'date';
    case DateTime = 'datetime';
    case Badge = 'badge';

    public function label(): string
    {
        return __("report::report.column_types.{$this->value}");
    }
}
