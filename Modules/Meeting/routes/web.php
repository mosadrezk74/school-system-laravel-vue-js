<?php

use Illuminate\Support\Facades\Route;
use Modules\Meeting\Http\Controllers\MeetingController;

Route::middleware(['web', 'auth'])->prefix('meetings')->group(function () {
    Route::resource('meetings', MeetingController::class);

    Route::patch('meetings/{meeting}/start', [MeetingController::class, 'start'])
        ->name('meetings.start');
    Route::patch('meetings/{meeting}/finish', [MeetingController::class, 'finish'])
        ->name('meetings.finish');
    Route::patch('meetings/{meeting}/cancel', [MeetingController::class, 'cancel'])
        ->name('meetings.cancel');
    Route::patch('timetable-entries/{entry}/start-meeting', [MeetingController::class, 'startForEntry'])
        ->name('meetings.startForEntry');
});
