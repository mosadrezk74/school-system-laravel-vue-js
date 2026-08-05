<?php

namespace Modules\Classroom\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class ClassroomOperationException extends BaseException
{
    public static function codeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('classroom::classroom.code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function classroomNotFound(string $message = ''): static
    {
        return new static($message ?: __('classroom::classroom.classroom_not_found'), HttpStatus::NOT_FOUND);
    }
}
