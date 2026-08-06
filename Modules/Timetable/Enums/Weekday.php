<?php

namespace Modules\Timetable\Enums;

use Modules\Core\Traits\BaseEnum;

enum Weekday: int
{
    use BaseEnum;

    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    public function label(): string
    {
        return __("timetable::timetable.days.{$this->name}");
    }
}
