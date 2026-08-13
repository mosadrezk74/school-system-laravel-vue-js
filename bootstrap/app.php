<?php

use App\Http\Middleware\EnsureSetupIsComplete;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\Core\Middlewares\EnsureModuleEnabled;
use Modules\Core\Middlewares\EnsurePortalAccess;
use Modules\Core\Middlewares\SetLocaleMiddleware;
use Modules\Core\Services\ErrorReportRecorder;
use Modules\Core\Support\ErrorResponder;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            // Runs before Inertia so shared props already carry the resolved
            // locale and its direction.
            SetLocaleMiddleware::class,
            HandleInertiaRequests::class,
            EnsureSetupIsComplete::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'module' => EnsureModuleEnabled::class,
            'portal' => EnsurePortalAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => app(ErrorResponder::class)->expectsJson($request),
        );

        /*
         * Every non-JSON failure becomes a themed Inertia page, so a normal
         * user never meets Laravel's debug screen or a bare Symfony page.
         * Validation, auth redirects and CSRF keep their framework behaviour:
         * turning a 422 into an error page would break every form in the SPA.
         */
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $responder = app(ErrorResponder::class);

            /*
             * Persist unexpected failures for the admin Error Center.
             * Recorded here rather than in report() because this is where the
             * offending request is actually available — report() receives only
             * the exception. Laravel's own logging is untouched and still runs
             * for every exception, so this is purely an additional sink.
             */
            if ($responder->shouldRecord($response->getStatusCode())) {
                app(ErrorReportRecorder::class)->record(
                    $exception,
                    $request,
                    $response->getStatusCode(),
                    $responder->errorCode($response->getStatusCode()),
                );
            }

            /*
             * API clients keep the ApiResponseTrait envelope the SPA and
             * mobile clients already parse, plus a stable error_code. The
             * message is the exception's own when the application raised it
             * deliberately, and the generic copy otherwise — see
             * ErrorResponder::hasIntentionalMessage() for why that line is
             * drawn at BaseException. Validation keeps Laravel's `errors` bag.
             */
            if ($responder->expectsJson($request)) {
                if ($exception instanceof ValidationException) {
                    return $response;
                }

                $statusCode = $response->getStatusCode();

                if ($statusCode < 400) {
                    return $response;
                }

                $payload = [
                    'status' => 'error',
                    'message' => $responder->userMessage($exception, $statusCode),
                    'error_code' => $responder->errorCode($statusCode),
                ];

                if (config('app.debug') && $statusCode >= 500) {
                    $payload['debug'] = [
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ];
                }

                return response()->json($payload, $statusCode);
            }

            if ($exception instanceof ValidationException || $exception instanceof AuthenticationException) {
                return $response;
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 400 || $request->isMethod('OPTIONS')) {
                return $response;
            }

            // Local debugging keeps the full Laravel error screen.
            if ($statusCode === 500 && config('app.debug')) {
                return $response;
            }

            return Inertia::render('Errors/ErrorPage', [
                'status' => $statusCode,
                'statusKey' => $responder->translationKey($statusCode),
                // Resolved server-side so the page reads correctly even when
                // it renders before the SPA locale bundle is available.
                'title' => $responder->title($statusCode),
                'description' => $responder->message($statusCode),

                /*
                 * The reason this specific request was refused, when the app
                 * raised it on purpose. Null otherwise, which is what tells
                 * the page to fall back to the generic copy — a value here
                 * always outranks the client's translation bundle.
                 */
                'detail' => $responder->hasIntentionalMessage($exception)
                    ? trim($exception->getMessage()) ?: null
                    : null,
            ])
                ->toResponse($request)
                ->setStatusCode($statusCode);
        });
    })->create();
