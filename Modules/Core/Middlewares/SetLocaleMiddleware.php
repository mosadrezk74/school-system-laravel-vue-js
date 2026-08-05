<?php

namespace Modules\Core\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Models\Language;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the request locale against the languages the school has enabled.
 *
 * Preference order: an explicit ?lang= override, the signed-in user's saved
 * language, then the session, then the school default. A locale that isn't
 * currently enabled is ignored — so switching a language off stops serving it
 * immediately, even to users who had already selected it.
 */
class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        app()->setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        // Persist on the user so the choice follows them to another device.
        $user = $request->user();
        if ($user && $user->language !== $locale) {
            $user->forceFill(['language' => $locale])->saveQuietly();
        }

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        $candidates = [
            $request->query('lang'),
            $request->user()?->language,
            $request->hasSession() ? $request->session()->get('locale') : null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && Language::isSupported($candidate)) {
                return $candidate;
            }
        }

        return Language::defaultLocale();
    }
}
