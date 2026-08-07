<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;
use Modules\Report\Http\Controllers\ReportTemplateController;

Route::middleware(['web', 'auth', 'module:Report'])->prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::post('generate', [ReportController::class, 'generate'])->name('reports.generate');

    Route::patch('templates/{reportTemplate}/restore', [ReportTemplateController::class, 'restore'])
        ->name('report-templates.restore');

    Route::resource('templates', ReportTemplateController::class)
        ->parameters(['templates' => 'reportTemplate'])
        ->names('report-templates')
        ->except(['show']);
});
