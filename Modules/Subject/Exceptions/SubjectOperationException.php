<?php

namespace Modules\Subject\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class SubjectOperationException extends BaseException
{
    public static function codeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('subject::subject.code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function subjectNotFound(string $message = ''): static
    {
        return new static($message ?: __('subject::subject.subject_not_found'), HttpStatus::NOT_FOUND);
    }
}
