<?php

namespace Modules\Student\Exceptions;

use Exception;

class StudentOperationException extends Exception
{
    public static function createFailed(string $message = 'Failed to create student.'): self
    {
        return new self($message, 400);
    }

    public static function updateFailed(string $message = 'Failed to update student.'): self
    {
        return new self($message, 400);
    }

    public static function deleteFailed(string $message = 'Failed to delete student.'): self
    {
        return new self($message, 400);
    }
}
