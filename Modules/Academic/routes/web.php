<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\AcademicYearController;

Route::middleware(['auth'])
    ->prefix('academic')
    ->name('academic.')
    ->group(function () {
        Route::resource('academic-years', AcademicYearController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::patch(
            'academic-years/{academicYear}/set-current',
            [AcademicYearController::class, 'setCurrent'],
        )->name('academic-years.set-current');
    });
