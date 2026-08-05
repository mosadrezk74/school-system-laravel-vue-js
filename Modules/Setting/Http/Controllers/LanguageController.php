<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Setting\Actions\SetDefaultLanguageAction;
use Modules\Setting\Actions\ToggleLanguageAction;
use Modules\Setting\Models\Language;
use Modules\Setting\Transformers\LanguageResource;

class LanguageController extends BaseInertiaController
{
    public function index(): Response
    {
        $this->authorize('viewAny', Language::class);

        // Enabled first, then alphabetical: the languages in use are what an
        // administrator came here to check.
        $languages = Language::query()
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        /*
         * Named `rows`, not `languages`: HandleInertiaRequests already shares a
         * `languages` prop (the enabled list the switcher reads), and a page
         * prop of the same name silently overwrites it.
         */
        return $this->render('Setting::Languages/Index', [
            'rows' => LanguageResource::collection($languages),
        ]);
    }

    public function toggle(Language $language, ToggleLanguageAction $action): RedirectResponse
    {
        $this->authorize('update', $language);

        $updated = $action->execute($language);

        return $this->backWithSuccess(__(
            $updated->is_active
                ? 'setting::languages.enabled'
                : 'setting::languages.disabled',
        ));
    }

    public function setDefault(Language $language, SetDefaultLanguageAction $action): RedirectResponse
    {
        $this->authorize('update', $language);

        $action->execute($language);

        return $this->backWithSuccess(__('setting::languages.default_set'));
    }
}
