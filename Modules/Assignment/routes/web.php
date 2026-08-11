<?php

use Illuminate\Support\Facades\Route;
use Modules\Assignment\Http\Controllers\AssignmentController;
use Modules\Assignment\Http\Controllers\AssignmentSubmissionController;

Route::middleware(['web', 'auth', 'module:Assignment'])->prefix('assignments')->group(function () {
    Route::resource('assignments', AssignmentController::class);

    Route::post('assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'store'])
        ->name('assignments.submissions.store');
    Route::patch('assignments/{assignment}/submissions/{submission}/grade', [AssignmentSubmissionController::class, 'grade'])
        ->name('assignments.submissions.grade');

    // Not part of the resources: they must resolve soft-deleted records, which
    // the default route-model binding on the other routes deliberately excludes.
    Route::patch('assignments/{assignment}/restore', [AssignmentController::class, 'restore'])
        ->name('assignments.restore');
});
