<?php

namespace Modules\Core\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Maps a thrown exception onto the status code and stable error code the rest
 * of the error handling works from.
 *
 * Kept separate from bootstrap/app.php so both the Inertia renderer and the
 * JSON renderer classify identically — a 403 must not be a 403 page on the web
 * and a 500 in the API.
 */
final class ErrorResponder
{
    /**
     * Status codes that are part of normal application flow. These are never
     * persisted to the Error Center: a mistyped URL is not an incident.
     */
    private const NOT_WORTH_RECORDING = [401, 403, 404, 419, 422, 429];

    public function statusCode(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof ValidationException => 422,
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AuthorizationException => 403,
            $exception instanceof TokenMismatchException => 419,
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof NotFoundHttpException => 404,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            default => 500,
        };
    }

    public function errorCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHENTICATED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            419 => 'PAGE_EXPIRED',
            422 => 'VALIDATION_FAILED',
            429 => 'TOO_MANY_REQUESTS',
            503 => 'SERVICE_UNAVAILABLE',
            default => 'INTERNAL_SERVER_ERROR',
        };
    }

    public function shouldRecord(int $statusCode): bool
    {
        return ! in_array($statusCode, self::NOT_WORTH_RECORDING, true);
    }

    /**
     * The user-facing message. Always a translation key lookup — the
     * exception's own message is never shown, since it can name internal
     * classes, tables or paths.
     */
    public function message(int $statusCode): string
    {
        return __('core::errors.status.'.$this->translationKey($statusCode).'.message');
    }

    public function title(int $statusCode): string
    {
        return __('core::errors.status.'.$this->translationKey($statusCode).'.title');
    }

    /**
     * Only the codes with dedicated copy get their own text; anything else is
     * presented as a generic server error.
     */
    public function translationKey(int $statusCode): string
    {
        return in_array($statusCode, [401, 403, 404, 419, 429, 503], true)
            ? (string) $statusCode
            : '500';
    }

    /**
     * Whether the request wants JSON rather than an HTML/Inertia page.
     */
    public function expectsJson(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
