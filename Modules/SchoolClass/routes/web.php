<?php

use Illuminate\Support\Facades\Route;
use Modules\SchoolClass\Http\Controllers\ClassController;

Route::middleware(['web', 'auth', 'module:SchoolClass'])->prefix('academic')->group(function () {
    // Batch section creation. Declared before the resource so `classes/sections`
    // is not swallowed by the `classes/{class}` show route.
    Route::post('classes/sections', [ClassController::class, 'storeSections'])
        ->name('classes.sections.store');

    Route::resource('classes', ClassController::class);

    // Not part of the resource: it must resolve soft-deleted classes, which
    // the default {class} binding on the other routes deliberately excludes.
    Route::patch('classes/{class}/restore', [ClassController::class, 'restore'])
        ->name('classes.restore');
});
