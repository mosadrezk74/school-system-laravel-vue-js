<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\ActivityController;
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

/*
 * The audit log, guarded the same way by ActivityPolicy. Read-only by design:
 * there is no write route because the log is written by the application and
 * pruned on a schedule, never edited by hand.
 */
Route::middleware(['auth'])
    ->prefix('system/activity')
    ->name('system.activity.')
    ->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('{activity}', [ActivityController::class, 'show'])->name('show');
    });
