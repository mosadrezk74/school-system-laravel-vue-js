<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Modules\Core\Middlewares\RedirectPortalUsers;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', RedirectPortalUsers::class])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
