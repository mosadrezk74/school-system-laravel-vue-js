<?php

namespace Modules\Report\Enums;

use Modules\Core\Traits\BaseEnum;

enum ReportCategory: string
{
    use BaseEnum;

    case Academic = 'academic';
    case Attendance = 'attendance';
    case Finance = 'finance';
    case Student = 'student';
    case Meeting = 'meeting';
    case Other = 'other';

    public function label(): string
    {
        return __("report::report.categories.{$this->value}");
    }
}
