<?php

namespace Modules\SchoolClass\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class ClassOperationException extends BaseException
{
    public static function classAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('school-class::class.class_already_exists'), HttpStatus::CONFLICT);
    }

    public static function classroomNotFound(string $message = ''): static
    {
        return new static($message ?: __('school-class::class.classroom_not_found'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function invalidCapacity(string $message = ''): static
    {
        return new static($message ?: __('school-class::class.invalid_capacity'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function classNotFound(string $message = ''): static
    {
        return new static($message ?: __('school-class::class.class_not_found'), HttpStatus::NOT_FOUND);
    }
}
