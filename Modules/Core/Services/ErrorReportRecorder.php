<?php

namespace Modules\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\Enums\ErrorReportStatus;
use Modules\Core\Models\ErrorReport;
use Modules\Core\Support\SensitiveDataRedactor;
use Throwable;

/**
 * Persists unexpected exceptions for the admin Error Center.
 *
 * This runs *alongside* Laravel's reporting, never instead of it: the
 * framework still writes to the configured log channels, and a failure here is
 * swallowed so error handling can never itself break the response.
 */
final readonly class ErrorReportRecorder
{
    public function __construct(
        private SensitiveDataRedactor $redactor,
    ) {}

    public function record(Throwable $exception, ?Request $request = null, ?int $statusCode = null, ?string $errorCode = null): ?ErrorReport
    {
        try {
            return $this->store($exception, $request, $statusCode, $errorCode);
        } catch (Throwable $failure) {
            // Never let diagnostics take down the actual response.
            Log::error('Failed to record error report: '.$failure->getMessage());

            return null;
        }
    }

    private function store(Throwable $exception, ?Request $request, ?int $statusCode, ?string $errorCode): ErrorReport
    {
        $route = $request?->route()?->getName() ?? $request?->path();
        $fingerprint = $this->fingerprint($exception, $route);
        $now = now();

        $existing = ErrorReport::query()->where('fingerprint', $fingerprint)->first();

        if ($existing) {
            $existing->forceFill([
                'occurrences' => $existing->occurrences + 1,
                'last_seen_at' => $now,
                // A recurrence means a "resolved" error is happening again.
                'status' => $existing->isResolved() ? ErrorReportStatus::New : $existing->status,
                'resolved_at' => $existing->isResolved() ? null : $existing->resolved_at,
                'resolved_by' => $existing->isResolved() ? null : $existing->resolved_by,
            ])->save();

            return $existing;
        }

        return ErrorReport::create([
            'fingerprint' => $fingerprint,
            'status' => ErrorReportStatus::New,
            'level' => 'error',
            'error_code' => $errorCode,
            'status_code' => $statusCode,
            'exception' => $exception::class,
            'message' => Str::limit($exception->getMessage(), 2000),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'route' => $route,
            'method' => $request?->method(),
            'url' => $request ? Str::limit($request->fullUrl(), 2000, '') : null,
            'request_id' => (string) Str::uuid(),
            'user_id' => $request?->user()?->id,
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 500, ''),
            'trace' => Str::limit($exception->getTraceAsString(), 60000),
            'context' => $this->context($request),
            'occurrences' => 1,
            'first_seen_at' => $now,
            'last_seen_at' => $now,
        ]);
    }

    /**
     * Groups identical failures.
     *
     * Built only from the exception class, its origin and the route — never
     * from request data, which could carry credentials. Digits in the message
     * are masked so "User 41 not found" and "User 92 not found" collapse into
     * one report instead of one row per id.
     */
    private function fingerprint(Throwable $exception, ?string $route): string
    {
        $normalizedMessage = preg_replace('/\d+/', '#', $exception->getMessage()) ?? '';

        return hash('sha256', implode('|', [
            $exception::class,
            $route ?? '',
            Str::limit($normalizedMessage, 200, ''),
            $exception->getFile(),
            (string) $exception->getLine(),
        ]));
    }

    /**
     * Safe request metadata only. Bodies and headers are never stored: this
     * app moves payment data, and a denylist over a whole payload is a weaker
     * guarantee than simply not collecting it.
     *
     * @return array<string, mixed>
     */
    private function context(?Request $request): array
    {
        if (! $request) {
            return [];
        }

        return $this->redactor->redact([
            'route_parameters' => $request->route()?->parameters() ?? [],
            'query_keys' => array_keys($request->query()),
            'input_keys' => array_keys($request->except(['password', 'password_confirmation'])),
            'locale' => app()->getLocale(),
        ]);
    }
}
