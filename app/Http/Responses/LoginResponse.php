<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Modules\Auth\Enums\UserRole;
use Modules\Setting\Services\SettingsService;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::Owner && ! app(SettingsService::class)->isSetupCompleted()) {
            return redirect()->route('onboarding.show');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
