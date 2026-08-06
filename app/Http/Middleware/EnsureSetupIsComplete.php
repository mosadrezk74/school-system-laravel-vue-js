<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Enums\UserRole;
use Modules\Setting\Services\SettingsService;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== UserRole::Owner) {
            return $next($request);
        }

        if ($request->routeIs('onboarding.*')) {
            return $next($request);
        }

        if (! app(SettingsService::class)->isSetupCompleted()) {
            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }
}
