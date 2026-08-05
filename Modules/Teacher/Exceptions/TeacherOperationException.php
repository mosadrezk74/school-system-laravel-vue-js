<?php

namespace Modules\Teacher\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class TeacherOperationException extends BaseException
{
    public static function emailAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('teacher::teacher.email_already_exists'), HttpStatus::CONFLICT);
    }

    public static function employeeIdAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('teacher::teacher.employee_id_already_exists'), HttpStatus::CONFLICT);
    }

    public static function userIsNotATeacher(string $message = ''): static
    {
        return new static($message ?: __('teacher::teacher.user_is_not_a_teacher'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function teacherNotFound(string $message = ''): static
    {
        return new static($message ?: __('teacher::teacher.teacher_not_found'), HttpStatus::NOT_FOUND);
    }
}
