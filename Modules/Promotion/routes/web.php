<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotion\Http\Controllers\PromotionController;

/*
 * Promotions sit under the student-management prefix because they act on
 * student records, and are gated by the module middleware so disabling the
 * module removes them entirely.
 */
Route::middleware(['web', 'auth', 'module:Promotion'])
    ->prefix('user-management/promotions')
    ->name('promotions.')
    ->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('index');
        Route::get('create', [PromotionController::class, 'create'])->name('create');
        Route::post('/', [PromotionController::class, 'store'])->name('store');

        Route::delete('{promotion}/revert', [PromotionController::class, 'revert'])
            ->name('revert');
    });
