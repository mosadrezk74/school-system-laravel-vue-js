<?php

use Illuminate\Support\Facades\Route;
use Modules\StudyMaterial\Http\Controllers\StudyResourceController;

Route::middleware(['web', 'auth', 'module:StudyMaterial'])->prefix('academic')->group(function () {
    Route::get('study-resources/{studyResource}/download', [StudyResourceController::class, 'download'])
        ->name('study-resources.download');

    Route::patch('study-resources/{studyResource}/restore', [StudyResourceController::class, 'restore'])
        ->name('study-resources.restore');

    Route::resource('study-resources', StudyResourceController::class)
        ->parameters(['study-resources' => 'studyResource']);
});
