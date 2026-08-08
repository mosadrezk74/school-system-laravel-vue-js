<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\LanguageController;
use Modules\Setting\Http\Controllers\OnboardingController;
use Modules\Setting\Http\Controllers\SchoolSettingController;

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

        Route::get('general', [SchoolSettingController::class, 'general'])->name('general');
        Route::post('general', [SchoolSettingController::class, 'updateGeneral'])->name('general.update');

        Route::get('organization', [SchoolSettingController::class, 'organization'])->name('organization');
        Route::post('organization', [SchoolSettingController::class, 'updateOrganization'])->name('organization.update');

        Route::get('modules', [SchoolSettingController::class, 'modules'])->name('modules');
        Route::patch('modules/{module}/toggle', [SchoolSettingController::class, 'toggleModule'])->name('modules.toggle');

        Route::get('ai-translation', [SchoolSettingController::class, 'aiTranslation'])->name('ai-translation');
        Route::post('ai-translation', [SchoolSettingController::class, 'updateAiTranslation'])->name('ai-translation.update');
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
