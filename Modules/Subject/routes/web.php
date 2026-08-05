<?php

use Illuminate\Support\Facades\Route;
use Modules\Subject\Http\Controllers\SubjectController;

Route::middleware(['web', 'auth'])->prefix('academic')->group(function () {
    Route::resource('subjects', SubjectController::class);
    Route::patch('subjects/{subject}/restore', [SubjectController::class, 'restore'])
        ->name('subjects.restore');
});
