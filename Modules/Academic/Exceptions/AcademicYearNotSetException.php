<?php

namespace Modules\Academic\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class AcademicYearNotSetException extends BaseException
{
    public static function missing(): self
    {
        return new self(
            __('academic::academic_years.no_current_year'),
            HttpStatus::UNPROCESSABLE_ENTITY,
        );
    }
}
