<?php

use Illuminate\Support\Facades\Route;
use Modules\Examination\Http\Controllers\ExamController;
use Modules\Examination\Http\Controllers\ExamGradeController;
use Modules\Examination\Http\Controllers\ExamSubjectController;

Route::middleware(['web', 'auth'])->prefix('examination')->group(function () {
    Route::resource('exams', ExamController::class);

    // Not part of the resource: it must resolve soft-deleted exams, which the
    // default {exam} binding on the other routes deliberately excludes.
    Route::patch('exams/{exam}/restore', [ExamController::class, 'restore'])
        ->name('exams.restore');

    Route::post('exams/{exam}/subjects', [ExamSubjectController::class, 'store'])
        ->name('exams.subjects.store');
    Route::put('exams/{exam}/subjects/{examSubject}', [ExamSubjectController::class, 'update'])
        ->name('exams.subjects.update');
    Route::delete('exams/{exam}/subjects/{examSubject}', [ExamSubjectController::class, 'destroy'])
        ->name('exams.subjects.destroy');

    Route::get('exams/{exam}/subjects/{examSubject}/results', [ExamController::class, 'createResults'])
        ->name('exams.subjects.results.create');
    Route::post('exams/{exam}/subjects/{examSubject}/results', [ExamController::class, 'storeResults'])
        ->name('exams.subjects.results.store');

    Route::get('grades', [ExamGradeController::class, 'index'])->name('exam-grades.index');
    Route::post('grades', [ExamGradeController::class, 'store'])->name('exam-grades.store');
    Route::put('grades/{examGrade}', [ExamGradeController::class, 'update'])->name('exam-grades.update');
    Route::delete('grades/{examGrade}', [ExamGradeController::class, 'destroy'])->name('exam-grades.destroy');
});
