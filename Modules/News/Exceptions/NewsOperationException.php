<?php

namespace Modules\News\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class NewsOperationException extends BaseException
{
    public static function notFound(string $message = ''): static
    {
        return new static($message ?: __('news::news.errors.not_found'), HttpStatus::NOT_FOUND);
    }

    public static function alreadyPublished(string $message = ''): static
    {
        return new static($message ?: __('news::news.errors.already_published'), HttpStatus::CONFLICT);
    }

    public static function notPublished(string $message = ''): static
    {
        return new static($message ?: __('news::news.errors.not_published'), HttpStatus::CONFLICT);
    }
}
