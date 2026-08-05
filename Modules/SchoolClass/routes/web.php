<?php

use Illuminate\Support\Facades\Route;
use Modules\SchoolClass\Http\Controllers\ClassController;

Route::middleware(['web', 'auth'])->prefix('academic')->group(function () {
    Route::resource('classes', ClassController::class);

    // Not part of the resource: it must resolve soft-deleted classes, which
    // the default {class} binding on the other routes deliberately excludes.
    Route::patch('classes/{class}/restore', [ClassController::class, 'restore'])
        ->name('classes.restore');
});
