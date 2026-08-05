<?php

namespace Modules\Core\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Core\Constants\HttpStatus;

class BaseException extends Exception
{
    protected array $errors = [];

    protected int $statusCode;

    public function __construct(
        string $message = 'Something went wrong',
        int $statusCode = HttpStatus::INTERNAL_SERVER_ERROR,
        array $errors = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->errors = $errors;
        $this->statusCode = $statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function render(): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $this->getMessage(),
        ];

        if (! empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return response()->json($response, $this->statusCode);
    }

    public static function notFound(string $message = ''): static
    {
        return new static($message ?: __('base.not_found'), HttpStatus::NOT_FOUND);
    }

    public static function unauthorized(string $message = ''): static
    {
        return new static($message ?: __('base.unauthorized'), HttpStatus::UNAUTHORIZED);
    }

    public static function forbidden(string $message = ''): static
    {
        return new static($message ?: __('base.forbidden'), HttpStatus::FORBIDDEN);
    }

    public static function alreadyExists(string $message = ''): static
    {
        return new static($message ?: __('base.already_exists'), HttpStatus::CONFLICT);
    }

    public static function badRequest(string $message = ''): static
    {
        return new static($message ?: __('base.bad_request'), HttpStatus::BAD_REQUEST);
    }

    public static function validation(array $errors, string $message = ''): static
    {
        return new static($message ?: __('base.validation_failed'), HttpStatus::UNPROCESSABLE_ENTITY, $errors);
    }

    public static function serverError(string $message = ''): static
    {
        return new static($message ?: __('base.server_error'), HttpStatus::INTERNAL_SERVER_ERROR);
    }

    public static function tooManyRequests(string $message = ''): static
    {
        return new static($message ?: __('base.too_many_requests'), HttpStatus::TOO_MANY_REQUESTS);
    }
}
