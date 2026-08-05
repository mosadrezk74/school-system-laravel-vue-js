<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
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

class AccountSettingsController extends BaseInertiaController
{
    /**
     * @return array<int, array{title: string, value: string}>
     */
    private function languages(): array
    {
        return [
            ['title' => __('auth::account.languages.en'), 'value' => 'en'],
            ['title' => __('auth::account.languages.ar'), 'value' => 'ar'],
            ['title' => __('auth::account.languages.de'), 'value' => 'de'],
            ['title' => __('auth::account.languages.fr'), 'value' => 'fr'],
        ];
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
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'passwordRules' => Password::default()->toPasswordRulesString(),
            'languages' => $this->languages(),
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
