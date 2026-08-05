<?php

namespace Modules\Attendance\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class AttendanceOperationException extends BaseException
{
    public static function recordNotFound(string $message = ''): static
    {
        return new static($message ?: __('attendance::attendance.record_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function classDoesNotBelongToYear(string $message = ''): static
    {
        return new static($message ?: __('attendance::attendance.class_does_not_belong_to_year'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
