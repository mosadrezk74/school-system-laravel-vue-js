<?php

use Illuminate\Support\Facades\Route;
use Modules\Branch\Http\Controllers\BranchController;

Route::middleware(['web', 'auth'])->prefix('settings')->group(function () {
    Route::resource('branches', BranchController::class);

    // Restore route for soft-deleted branches
    Route::patch('branches/{branch}/restore', [BranchController::class, 'restore'])
        ->name('branches.restore');
});