<?php

namespace Modules\Core\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Modules\Core\Exceptions\BaseException;
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
     * The generic copy for a status code, used whenever the exception has
     * nothing safe of its own to say.
     */
    public function message(int $statusCode): string
    {
        return __('core::errors.status.'.$this->translationKey($statusCode).'.message');
    }

    /**
     * What the user actually reads.
     *
     * Prefers the exception's own message when the application raised it
     * deliberately — "this year is not active" is far more useful than "an
     * unexpected error occurred". Everything else keeps the generic copy.
     */
    public function userMessage(Throwable $exception, int $statusCode): string
    {
        $message = trim($exception->getMessage());

        if ($message !== '' && $this->hasIntentionalMessage($exception)) {
            return $message;
        }

        return $this->message($statusCode);
    }

    /**
     * Whether an exception's message was written for a user to read.
     *
     * Only BaseException qualifies: every one of its factories, and every
     * module exception extending it, is built from a translation key. The rule
     * is deliberately narrow — a raw HttpException is not enough, because
     * Laravel turns a failed route-model binding into one whose message names
     * the model class ("No query results for model [...]"), and a QueryException
     * would spill SQL and column names onto the page.
     */
    public function hasIntentionalMessage(Throwable $exception): bool
    {
        return $exception instanceof BaseException;
    }

    public function title(int $statusCode): string
    {
        return __('core::errors.status.'.$this->translationKey($statusCode).'.title');
    }

    /**
     * Only the codes with dedicated copy get their own text; anything else is
     * presented as a generic server error.
     *
     * 400 and 422 are here because the app raises them on purpose — a refused
     * operation was being titled "an unexpected error occurred", which reads
     * as a crash rather than as the rule it actually is.
     */
    public function translationKey(int $statusCode): string
    {
        return in_array($statusCode, [400, 401, 403, 404, 419, 422, 429, 503], true)
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
