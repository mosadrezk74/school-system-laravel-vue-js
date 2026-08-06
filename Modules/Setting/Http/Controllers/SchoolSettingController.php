<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Services\ModuleRegistry;
use Modules\Setting\Actions\ToggleModuleAction;
use Modules\Setting\Actions\UpdateAiTranslationSettingsAction;
use Modules\Setting\Actions\UpdateOrganizationSettingsAction;
use Modules\Setting\Actions\UpdateSchoolInfoAction;
use Modules\Setting\DTOs\UpdateAiTranslationSettingsDTO;
use Modules\Setting\DTOs\UpdateOrganizationSettingsDTO;
use Modules\Setting\DTOs\UpdateSchoolInfoDTO;
use Modules\Setting\Http\Requests\UpdateAiTranslationSettingsRequest;
use Modules\Setting\Http\Requests\UpdateOrganizationSettingsRequest;
use Modules\Setting\Http\Requests\UpdateSchoolInfoRequest;
use Modules\Setting\Models\SchoolSetting;
use Modules\Setting\Services\SettingsService;
use Modules\Setting\Transformers\SchoolSettingResource;

class SchoolSettingController extends BaseInertiaController
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly SettingsService $settings,
    ) {}

    public function general(): Response
    {
        $this->authorize('view', SchoolSetting::class);

        return $this->render('Setting::Settings/General', [
            'schoolSetting' => new SchoolSettingResource($this->settings->get() ?? new SchoolSetting),
        ]);
    }

    public function updateGeneral(UpdateSchoolInfoRequest $request, UpdateSchoolInfoAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute(UpdateSchoolInfoDTO::fromRequest($request));

        return $this->backWithSuccess(__('setting::onboarding.school_info_saved'));
    }

    public function organization(): Response
    {
        $this->authorize('view', SchoolSetting::class);

        return $this->render('Setting::Settings/Organization', [
            'schoolSetting' => new SchoolSettingResource($this->settings->get() ?? new SchoolSetting),
        ]);
    }

    public function updateOrganization(UpdateOrganizationSettingsRequest $request, UpdateOrganizationSettingsAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute(UpdateOrganizationSettingsDTO::fromRequest($request));

        return $this->backWithSuccess(__('setting::onboarding.organization_saved'));
    }

    public function modules(): Response
    {
        $this->authorize('view', SchoolSetting::class);

        return $this->render('Setting::Settings/Modules', [
            'modules' => $this->registry->all(),
        ]);
    }

    public function toggleModule(string $module, ToggleModuleAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute([
            'name' => $module,
            'enable' => ! $this->registry->isEnabled($module),
        ]);

        return $this->backWithSuccess(__('setting::onboarding.module_toggled'));
    }

    public function aiTranslation(): Response
    {
        $this->authorize('view', SchoolSetting::class);

        return $this->render('Setting::Settings/AiTranslation', [
            'schoolSetting' => new SchoolSettingResource($this->settings->get() ?? new SchoolSetting),
        ]);
    }

    public function updateAiTranslation(UpdateAiTranslationSettingsRequest $request, UpdateAiTranslationSettingsAction $action): RedirectResponse
    {
        $this->authorize('update', SchoolSetting::class);

        $action->execute(UpdateAiTranslationSettingsDTO::fromRequest($request));

        return $this->backWithSuccess(__('setting::onboarding.ai_translation_saved'));
    }
}
