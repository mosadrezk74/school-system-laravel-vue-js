<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Setting\Actions\CompleteSetupAction;
use Modules\Setting\Actions\SetDefaultLanguageAction;
use Modules\Setting\Actions\ToggleLanguageAction;
use Modules\Setting\Actions\UpdateSchoolInfoAction;
use Modules\Setting\DTOs\UpdateSchoolInfoDTO;
use Modules\Setting\Http\Requests\UpdateSchoolInfoRequest;
use Modules\Setting\Models\Language;
use Modules\Setting\Models\SchoolSetting;
use Modules\Setting\Services\LanguageService;
use Modules\Setting\Services\SettingsService;
use Modules\Setting\Transformers\LanguageResource;
use Modules\Setting\Transformers\SchoolSettingResource;

class OnboardingController extends BaseInertiaController
{
    public function __construct(
        private readonly LanguageService $languages,
        private readonly SettingsService $settings,
    ) {}

    public function show(): Response
    {
        $this->authorize('update', SchoolSetting::class);

        $setting = $this->settings->get();

        return $this->render('Setting::Onboarding/Wizard', [
            'schoolSetting' => $setting ? new SchoolSettingResource($setting) : null,
            // Not `languages`: HandleInertiaRequests/SettingServiceProvider
            // already shares a `languages` prop (the enabled list every page
            // reads for the locale switcher), and a page prop of the same
            // name silently overwrites it.
            'rows' => LanguageResource::collection($this->languages->all()),
        ]);
    }

    public function updateSchoolInfo(UpdateSchoolInfoRequest $request, UpdateSchoolInfoAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute(UpdateSchoolInfoDTO::fromRequest($request));

        return $this->backWithSuccess(__('setting::onboarding.school_info_saved'));
    }

    public function toggleLanguage(Language $language, ToggleLanguageAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $updated = $action->execute($language);

        return $this->backWithSuccess(__(
            $updated->is_active ? 'setting::languages.enabled' : 'setting::languages.disabled'
        ));
    }

    public function setDefaultLanguage(Language $language, SetDefaultLanguageAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute($language);

        return $this->backWithSuccess(__('setting::languages.default_set'));
    }

    public function finish(CompleteSetupAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute(null);

        return $this->toRoute('dashboard');
    }
}
