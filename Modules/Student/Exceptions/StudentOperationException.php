<?php

namespace Modules\Student\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class StudentOperationException extends BaseException
{
    public static function emailAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('student::student.email_already_exists'), HttpStatus::CONFLICT);
    }

    public static function admissionNumberAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('student::student.admission_number_already_exists'), HttpStatus::CONFLICT);
    }

    public static function userIsNotAStudent(string $message = ''): static
    {
        return new static($message ?: __('student::student.user_is_not_a_student'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function studentNotFound(string $message = ''): static
    {
        return new static($message ?: __('student::student.student_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function classDoesNotBelongToYear(string $message = ''): static
    {
        return new static($message ?: __('student::student.class_does_not_belong_to_year'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function academicYearNotActive(string $message = ''): static
    {
        return new static($message ?: __('student::student.academic_year_not_active'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function incompatibleAcademicData(string $message = ''): static
    {
        return new static($message ?: __('student::student.incompatible_academic_data'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
