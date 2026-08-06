<?php

namespace Modules\Meeting\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class MeetingOperationException extends BaseException
{
    public static function meetingNotFound(string $message = ''): static
    {
        return new static($message ?: __('meeting::meeting.meeting_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function invalidStatusTransition(string $message = ''): static
    {
        return new static($message ?: __('meeting::meeting.invalid_status_transition'), HttpStatus::CONFLICT);
    }

    public static function roomNameAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('meeting::meeting.room_name_already_exists'), HttpStatus::CONFLICT);
    }
}
