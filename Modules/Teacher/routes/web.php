<?php

use Illuminate\Support\Facades\Route;
use Modules\Teacher\Http\Controllers\TeacherController;

Route::middleware(['web', 'auth'])->prefix('user-management')->group(function () {
    Route::resource('teachers', TeacherController::class);
    Route::patch('teachers/{teacher}/restore', [TeacherController::class, 'restore'])
        ->name('teachers.restore');
});
