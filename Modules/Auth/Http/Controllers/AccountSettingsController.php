<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Response;
use Modules\Auth\Actions\UpdatePasswordAction;
use Modules\Auth\Actions\UpdateProfileAction;
use Modules\Auth\DTOs\UpdatePasswordDTO;
use Modules\Auth\DTOs\UpdateProfileDTO;
use Modules\Auth\Http\Requests\DeleteProfileRequest;
use Modules\Auth\Http\Requests\UpdatePasswordRequest;
use Modules\Auth\Http\Requests\UpdateProfileRequest;
use Modules\Auth\Transformers\AccountResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Setting\Services\LanguageService;

class AccountSettingsController extends BaseInertiaController
{
    public function __construct(private readonly LanguageService $languages) {}

    /**
     * @return array<int, array{title: string, value: string}>
     */
    private function languageOptions(): array
    {
        return $this->languages->active()
            ->map(fn (array $language) => ['title' => $language['name'], 'value' => $language['locale']])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{title: string, value: string}>
     */
    private function themes(): array
    {
        return [
            ['title' => __('auth::account.themes.light'), 'value' => 'light'],
            ['title' => __('auth::account.themes.dark'), 'value' => 'dark'],
        ];
    }

    /**
     * @return array<int, array{title: string, value: string}>
     */
    private function timezones(): array
    {
        $zones = ['Asia/Riyadh', 'Asia/Dubai', 'Asia/Kuwait', 'Asia/Qatar', 'Africa/Cairo', 'UTC', 'Europe/London', 'America/New_York'];

        return array_map(static fn (string $zone): array => ['title' => $zone, 'value' => $zone], $zones);
    }

    public function edit(Request $request): Response
    {
        return $this->render('Auth::AccountSettings/Index', [
            'account' => new AccountResource($request->user()),
            'passwordRules' => Password::default()->toPasswordRulesString(),
            // Not `languages`: HandleInertiaRequests/SettingServiceProvider
            // already shares a `languages` prop (the enabled list every page
            // reads for the locale switcher), and a page prop of the same
            // name silently overwrites it.
            'languageOptions' => $this->languageOptions(),
            'themes' => $this->themes(),
            'timezones' => $this->timezones(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $action->execute([
            'user' => $request->user(),
            'dto' => UpdateProfileDTO::fromRequest($request),
        ]);

        return $this->backWithSuccess(__('auth::account.profile_updated'));
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): RedirectResponse
    {
        $action->execute([
            'user' => $request->user(),
            'dto' => UpdatePasswordDTO::fromRequest($request),
        ]);

        return $this->backWithSuccess(__('auth::account.password_updated'));
    }

    public function destroy(DeleteProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
