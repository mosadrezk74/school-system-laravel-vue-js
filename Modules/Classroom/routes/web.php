<?php

use Illuminate\Support\Facades\Route;
use Modules\Classroom\Http\Controllers\ClassroomController;

Route::middleware(['web', 'auth'])->prefix('academic')->group(function () {
    Route::resource('classrooms', ClassroomController::class);
    Route::patch('classrooms/{classroom}/restore', [ClassroomController::class, 'restore'])
        ->name('classrooms.restore');
});
