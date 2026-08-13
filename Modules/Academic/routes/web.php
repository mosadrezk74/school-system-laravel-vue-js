<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\AcademicYearController;
use Modules\Academic\Http\Controllers\GradeLevelController;
use Modules\Academic\Http\Controllers\SemesterController;

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

        Route::patch(
            'semesters/{semester}/set-current',
            [SemesterController::class, 'setCurrent'],
        )->name('semesters.set-current');

        /*
         * Restore is declared before the resource so `semesters/{semester}`
         * cannot swallow the more specific path, and takes the raw id because
         * route-model binding excludes soft-deleted rows.
         */
        Route::patch('semesters/{semester}/restore', [SemesterController::class, 'restore'])
            ->name('semesters.restore');

        Route::resource('semesters', SemesterController::class);

        Route::patch('grade-levels/{gradeLevel}/restore', [GradeLevelController::class, 'restore'])
            ->name('grade-levels.restore');

        /*
         * The parameter is named explicitly: Laravel would derive
         * `{grade_level}` from the URI, which does not match the
         * `GradeLevel $gradeLevel` type hint and so never resolves the model.
         */
        Route::resource('grade-levels', GradeLevelController::class)
            ->parameters(['grade-levels' => 'gradeLevel']);
    });
