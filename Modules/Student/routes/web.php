<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;

Route::middleware(['web', 'auth', 'module:Student'])->prefix('user-management')->group(function () {
    Route::resource('students', StudentController::class);

    // Not part of the resource: it must resolve soft-deleted profiles, which
    // the default {student} binding on the other routes deliberately excludes.
    Route::patch('students/{student}/restore', [StudentController::class, 'restore'])
        ->name('students.restore');
});
