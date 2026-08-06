<?php

namespace Modules\Examination\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class ExamOperationException extends BaseException
{
    public static function codeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('examination::exam.code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function examNotFound(string $message = ''): static
    {
        return new static($message ?: __('examination::exam.exam_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function examSubjectNotConfigured(string $message = ''): static
    {
        return new static($message ?: __('examination::exam.exam_subject_not_configured'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function invalidPercentageRange(string $message = ''): static
    {
        return new static($message ?: __('examination::exam_grade.invalid_percentage_range'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function gradeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('examination::exam_grade.grade_already_exists'), HttpStatus::CONFLICT);
    }
}
