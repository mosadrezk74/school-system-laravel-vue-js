<?php

namespace Modules\Timetable\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class TimetableOperationException extends BaseException
{
    public static function classAlreadyHasTimetableForYear(string $message = ''): static
    {
        return new static($message ?: __('timetable::timetable.class_already_has_timetable_for_year'), HttpStatus::CONFLICT);
    }

    public static function duplicatePeriodNumber(string $message = ''): static
    {
        return new static($message ?: __('timetable::timetable.duplicate_period_number'), HttpStatus::CONFLICT);
    }

    public static function overlappingPeriodTime(string $message = ''): static
    {
        return new static($message ?: __('timetable::timetable.overlapping_period_time'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    /**
     * @param  array<int, string>  $messages
     */
    public static function conflicts(array $messages): static
    {
        return new static(
            __('timetable::timetable.entries_conflict'),
            HttpStatus::UNPROCESSABLE_ENTITY,
            $messages
        );
    }
}
