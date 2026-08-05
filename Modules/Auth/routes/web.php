<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AccountSettingsController;

Route::middleware(['auth'])->prefix('account-settings')->name('account-settings.')->group(function () {
    Route::get('/', [AccountSettingsController::class, 'edit'])->name('edit');
    Route::patch('profile', [AccountSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::put('password', [AccountSettingsController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('password.update');
    Route::delete('/', [AccountSettingsController::class, 'destroy'])->name('destroy');
});
