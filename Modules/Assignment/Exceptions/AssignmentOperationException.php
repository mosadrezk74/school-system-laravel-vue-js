<?php

namespace Modules\Assignment\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class AssignmentOperationException extends BaseException
{
    public static function assignmentNotFound(string $message = ''): static
    {
        return new static($message ?: __('assignment::assignment.assignment_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function submissionAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('assignment::assignment.submission_already_exists'), HttpStatus::CONFLICT);
    }

    public static function submissionNotFound(string $message = ''): static
    {
        return new static($message ?: __('assignment::assignment.submission_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function invalidTeachingAssignment(string $message = ''): static
    {
        return new static(
            $message ?: __('assignment::assignment.validation.teacher_not_assigned_to_class_subject'),
            HttpStatus::UNPROCESSABLE_ENTITY,
        );
    }
}
