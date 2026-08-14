<?php

namespace Modules\Communication\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class ConversationOperationException extends BaseException
{
    public static function notAParticipant(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.not_a_participant'), HttpStatus::FORBIDDEN);
    }

    /** The signed-in user may not open a thread with one of the chosen people. */
    public static function ineligibleParticipants(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.ineligible_participants'), HttpStatus::FORBIDDEN);
    }

    /** A message with neither text nor a file carries nothing. */
    public static function emptyMessage(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.empty_message'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function directRequiresExactlyOneOther(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.direct_requires_one_other'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function groupRequiresName(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.group_requires_name'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    /** The media row exists but hangs off some other model or collection. */
    public static function attachmentNotFound(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.attachment_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function attachmentMissingFromDisk(string $message = ''): static
    {
        return new static($message ?: __('communication::chat.errors.attachment_missing'), HttpStatus::NOT_FOUND);
    }
}
