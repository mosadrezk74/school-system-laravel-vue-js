<?php

namespace Modules\Academic\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class SemesterOperationException extends BaseException
{
    public static function sequenceAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('academic::semesters.sequence_already_exists'), HttpStatus::CONFLICT);
    }

    public static function notFound(string $message = ''): static
    {
        return new static($message ?: __('academic::semesters.not_found'), HttpStatus::NOT_FOUND);
    }
}
