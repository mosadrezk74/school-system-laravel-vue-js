<?php

use Illuminate\Support\Facades\Route;
use Modules\Meeting\Http\Controllers\MeetingController;

Route::middleware(['web', 'auth', 'module:Meeting'])->prefix('meetings')->group(function () {
    /*
     * Declared before the resource so `meetings/{meeting}/join` is not
     * captured by the resource's show route.
     */
    Route::get('meetings/{meeting}/join', [MeetingController::class, 'join'])
        ->name('meetings.join');

    /*
     * Polled by an open room so that finishing or cancelling the meeting
     * elsewhere also closes it for whoever is still inside.
     */
    Route::get('meetings/{meeting}/status', [MeetingController::class, 'status'])
        ->name('meetings.status');

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
