<?php

namespace Modules\Setting\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class SchoolTermOperationException extends BaseException
{
    /**
     * Families accept a specific wording, so a published version's text is
     * frozen — changing it would invalidate every acceptance already given.
     */
    public static function publishedCannotBeEdited(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.published_cannot_be_edited'), HttpStatus::FORBIDDEN);
    }

    public static function onlyDraftCanBePublished(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.only_draft_can_be_published'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function onlyDraftCanBeDeleted(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.only_draft_can_be_deleted'), HttpStatus::FORBIDDEN);
    }

    public static function draftAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.draft_already_exists'), HttpStatus::CONFLICT);
    }

    public static function notAcceptable(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.not_acceptable'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function alreadyAccepted(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.already_accepted'), HttpStatus::CONFLICT);
    }

    public static function noPublishedVersion(string $message = ''): static
    {
        return new static($message ?: __('setting::school_terms.errors.no_published_version'), HttpStatus::NOT_FOUND);
    }
}
