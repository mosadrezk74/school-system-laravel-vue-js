<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\LanguageController;
use Modules\Setting\Http\Controllers\OnboardingController;
use Modules\Setting\Http\Controllers\PreferenceController;
use Modules\Setting\Http\Controllers\SchoolSettingController;
use Modules\Setting\Http\Controllers\SchoolTermController;
use Modules\Setting\Http\Controllers\SchoolTermPortalController;

/*
 * Reading and accepting the school's terms is open to every signed-in user,
 * whichever portal they belong to, so these sit at a neutral path rather than
 * under /settings (staff) or /student and /parent (one audience each).
 */
Route::middleware(['auth'])
    ->prefix('terms')
    ->name('school-terms.')
    ->group(function () {
        Route::get('/', [SchoolTermPortalController::class, 'show'])->name('show');
        Route::post('accept', [SchoolTermPortalController::class, 'accept'])->name('accept');
    });

Route::middleware(['auth'])
    ->prefix('settings')
    ->name('settings.')
    ->group(function () {
        Route::get('languages', [LanguageController::class, 'index'])
            ->name('languages.index');

        Route::patch('languages/{language}/toggle', [LanguageController::class, 'toggle'])
            ->name('languages.toggle');

        Route::patch('languages/{language}/default', [LanguageController::class, 'setDefault'])
            ->name('languages.default');

        Route::patch('theme', [PreferenceController::class, 'updateTheme'])->name('theme.update');

        Route::get('general', [SchoolSettingController::class, 'general'])->name('general');
        Route::post('general', [SchoolSettingController::class, 'updateGeneral'])->name('general.update');

        Route::get('organization', [SchoolSettingController::class, 'organization'])->name('organization');
        Route::post('organization', [SchoolSettingController::class, 'updateOrganization'])->name('organization.update');

        Route::get('modules', [SchoolSettingController::class, 'modules'])->name('modules');
        Route::patch('modules/{module}/toggle', [SchoolSettingController::class, 'toggleModule'])->name('modules.toggle');

        Route::get('ai-translation', [SchoolSettingController::class, 'aiTranslation'])->name('ai-translation');
        Route::post('ai-translation', [SchoolSettingController::class, 'updateAiTranslation'])->name('ai-translation.update');

        // Declared before the resource so `school-terms/{schoolTerm}` cannot
        // swallow the more specific path.
        Route::patch('school-terms/{schoolTerm}/publish', [SchoolTermController::class, 'publish'])
            ->name('school-terms.publish');

        /*
         * The parameter is named explicitly: Laravel would derive
         * `{school_term}` from the URI, which does not match the
         * `SchoolTerm $schoolTerm` type hint and so never resolves the model.
         */
        Route::resource('school-terms', SchoolTermController::class)
            ->parameters(['school-terms' => 'schoolTerm'])
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    });

Route::middleware(['auth'])
    ->prefix('onboarding')
    ->name('onboarding.')
    ->group(function () {
        Route::get('/', [OnboardingController::class, 'show'])->name('show');
        Route::post('school-info', [OnboardingController::class, 'updateSchoolInfo'])->name('school-info');
        Route::patch('languages/{language}/toggle', [OnboardingController::class, 'toggleLanguage'])->name('languages.toggle');
        Route::patch('languages/{language}/default', [OnboardingController::class, 'setDefaultLanguage'])->name('languages.default');
        Route::post('finish', [OnboardingController::class, 'finish'])->name('finish');
    });
