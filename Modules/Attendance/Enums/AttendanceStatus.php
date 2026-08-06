<?php

namespace Modules\Attendance\Enums;

use Modules\Core\Traits\BaseEnum;

enum AttendanceStatus: string
{
    use BaseEnum;

    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';
    case Other = 'other';

    public function label(): string
    {
        return __("attendance::attendance.statuses.{$this->value}");
    }
}
