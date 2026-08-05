<?php

namespace Modules\GuardianProfile\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class GuardianOperationException extends BaseException
{
    public static function emailAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('guardian::guardian.email_already_exists'), HttpStatus::CONFLICT);
    }

    public static function usernameAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('guardian::guardian.username_already_exists'), HttpStatus::CONFLICT);
    }

    public static function guardianNotFound(string $message = ''): static
    {
        return new static($message ?: __('guardian::guardian.guardian_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function studentAlreadyLinked(string $message = ''): static
    {
        return new static($message ?: __('guardian::guardian.student_already_linked'), HttpStatus::CONFLICT);
    }
}
