<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Setting\Http\Requests\UpdateThemeRequest;

/**
 * Per-user interface preferences, stored on the user row so a choice made on
 * one device follows the account instead of living only in that browser.
 */
class PreferenceController extends BaseInertiaController
{
    public function updateTheme(UpdateThemeRequest $request): RedirectResponse
    {
        $request->user()->update([
            'theme' => $request->validated('theme'),
        ]);

        return back();
    }
}
