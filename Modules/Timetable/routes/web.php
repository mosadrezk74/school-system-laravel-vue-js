<?php

use Illuminate\Support\Facades\Route;
use Modules\Timetable\Http\Controllers\PeriodController;
use Modules\Timetable\Http\Controllers\TimetableController;
use Modules\Timetable\Http\Controllers\TimetableEntryController;
use Modules\Timetable\Http\Controllers\WeekdaySettingController;

Route::middleware(['web', 'auth', 'module:Timetable'])->prefix('schedule')->group(function () {
    Route::resource('periods', PeriodController::class)->except(['show']);
    Route::patch('periods/{period}/restore', [PeriodController::class, 'restore'])->name('periods.restore');
    Route::patch('weekday-settings', [WeekdaySettingController::class, 'update'])->name('weekday-settings.update');

    Route::resource('timetables', TimetableController::class);
    Route::patch('timetables/{timetable}/restore', [TimetableController::class, 'restore'])->name('timetables.restore');
    Route::put('timetables/{timetable}/entries', [TimetableEntryController::class, 'update'])->name('timetables.entries.update');
});
