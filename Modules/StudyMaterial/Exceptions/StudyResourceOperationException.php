<?php

namespace Modules\StudyMaterial\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class StudyResourceOperationException extends BaseException
{
    public static function fileOrUrlRequired(string $message = ''): static
    {
        return new static($message ?: __('studymaterial::study_resource.file_or_url_required'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function noFileToDownload(string $message = ''): static
    {
        return new static($message ?: __('studymaterial::study_resource.no_file_to_download'), HttpStatus::NOT_FOUND);
    }

    public static function teacherIsNotATeacher(string $message = ''): static
    {
        return new static($message ?: __('studymaterial::study_resource.teacher_is_not_a_teacher'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
