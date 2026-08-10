<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\ErrorReportController;

/*
 * System diagnostics. Access is enforced by ErrorReportPolicy on every action
 * — the sidebar entry is only a convenience, never the security boundary.
 */
Route::middleware(['auth'])
    ->prefix('system/errors')
    ->name('system.errors.')
    ->group(function () {
        Route::get('/', [ErrorReportController::class, 'index'])->name('index');
        Route::get('{errorReport}', [ErrorReportController::class, 'show'])->name('show');
        Route::patch('{errorReport}', [ErrorReportController::class, 'update'])->name('update');
    });
