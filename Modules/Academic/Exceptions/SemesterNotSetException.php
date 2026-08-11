<?php

namespace Modules\Academic\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class SemesterNotSetException extends BaseException
{
    public static function missing(): self
    {
        return new self(
            __('academic::semesters.no_current_semester'),
            HttpStatus::UNPROCESSABLE_ENTITY,
        );
    }
}
