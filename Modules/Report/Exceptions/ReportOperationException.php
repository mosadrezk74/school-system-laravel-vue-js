<?php

namespace Modules\Report\Exceptions;

use Modules\Core\Constants\HttpStatus;
use Modules\Core\Exceptions\BaseException;

class ReportOperationException extends BaseException
{
    public static function providerNotFound(string $message = ''): static
    {
        return new static($message ?: __('report::report.provider_not_found'), HttpStatus::NOT_FOUND);
    }

    public static function codeAlreadyExists(string $message = ''): static
    {
        return new static($message ?: __('report::report.code_already_exists'), HttpStatus::CONFLICT);
    }

    public static function templateHasGenerations(string $message = ''): static
    {
        return new static($message ?: __('report::report.template_has_generations'), HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public static function generationFailed(string $message = ''): static
    {
        return new static($message ?: __('report::report.generation_failed'), HttpStatus::UNPROCESSABLE_ENTITY);
    }
}
