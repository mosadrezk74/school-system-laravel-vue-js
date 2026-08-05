<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\LanguageController;

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
    });
