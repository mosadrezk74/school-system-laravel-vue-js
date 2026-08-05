<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;

Route::middleware(['web', 'auth'])->prefix('user-management')->group(function () {
    Route::resource('students', StudentController::class);
});
