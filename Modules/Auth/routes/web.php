<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Http\Controllers\AccountSettingsController;
use Modules\Auth\Http\Controllers\AdminController;
use Modules\Auth\Http\Controllers\RoleController;

/*
 * Roles and administrative accounts sit under one access-control prefix,
 * separate from /user-management, which is reserved for the people-facing
 * profile modules (students, teachers, guardians).
 */
Route::middleware(['auth'])
    ->prefix('access-control')
    ->name('auth.')
    ->group(function () {
        Route::resource('roles', RoleController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        /*
         * {admin} resolves only owner/admin accounts. Without this binding a
         * crafted id would let the admin screen edit a student's user row and
         * hand it staff roles.
         */
        Route::bind('admin', fn (string $value): User => User::query()
            ->whereIn('role', [UserRole::Owner->value, UserRole::Admin->value])
            ->findOrFail($value));

        Route::patch('admins/{admin}/activation', [AdminController::class, 'toggleActivation'])
            ->name('admins.activation');

        Route::resource('admins', AdminController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });

Route::middleware(['auth'])->prefix('account-settings')->name('account-settings.')->group(function () {
    Route::get('/', [AccountSettingsController::class, 'edit'])->name('edit');
    Route::patch('profile', [AccountSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::put('password', [AccountSettingsController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('password.update');
    Route::delete('/', [AccountSettingsController::class, 'destroy'])->name('destroy');
});
