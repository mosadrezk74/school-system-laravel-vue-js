<?php

use Illuminate\Support\Facades\Route;
use Modules\GuardianProfile\Http\Controllers\GuardianController;

Route::middleware(['web', 'auth', 'module:GuardianProfile'])->prefix('user-management')->group(function () {
    Route::resource('guardians', GuardianController::class);

    // Not part of the resource: it must resolve soft-deleted profiles, which
    // the default {guardian} binding on the other routes deliberately excludes.
    Route::patch('guardians/{guardian}/restore', [GuardianController::class, 'restore'])
        ->name('guardians.restore');

    Route::post('guardians/{guardian}/students', [GuardianController::class, 'linkStudent'])
        ->name('guardians.students.link');
    Route::delete('guardians/{guardian}/students/{student}', [GuardianController::class, 'unlinkStudent'])
        ->name('guardians.students.unlink');
});
