<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware(['web', 'auth', 'module:Notification'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::patch('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::patch('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
});
