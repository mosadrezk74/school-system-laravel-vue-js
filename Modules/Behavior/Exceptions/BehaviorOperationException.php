<?php

namespace Modules\Behavior\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class BehaviorOperationException extends BaseException
{
    public static function studentNotFound(string $message = ''): static
    {
        return new static($message ?: __('behavior::behavior.student_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function categoryNotFound(string $message = ''): static
    {
        return new static($message ?: __('behavior::behavior.category_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function categoryInactive(string $message = ''): static
    {
        return new static($message ?: __('behavior::behavior.category_inactive'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
