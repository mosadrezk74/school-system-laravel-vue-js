<?php

use Illuminate\Support\Facades\Route;
use Modules\Communication\Http\Controllers\EmailBroadcastController;
use Modules\Communication\Http\Controllers\MessageController;

Route::middleware(['web', 'auth', 'module:Communication'])->prefix('communication')->name('communication.')->group(function () {
    Route::resource('messages', MessageController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('email-broadcast', [EmailBroadcastController::class, 'create'])->name('emailBroadcast.create');
    Route::post('email-broadcast', [EmailBroadcastController::class, 'store'])->name('emailBroadcast.store');
});
