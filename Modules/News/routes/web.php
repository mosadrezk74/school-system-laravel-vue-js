<?php

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\NewsController;
use Modules\News\Http\Controllers\NewsFeedController;

Route::middleware(['web', 'auth', 'module:News'])->group(function () {
    /*
     * The feed sits at a neutral path: it is the same screen for students,
     * guardians and teachers, so it belongs to none of their portal prefixes.
     */
    Route::get('news', [NewsFeedController::class, 'index'])->name('news.feed');

    Route::prefix('communication/news')->name('news.')->group(function () {
        // Declared before the resource so they cannot be swallowed by {news}.
        Route::patch('{news}/publish', [NewsController::class, 'publish'])->name('publish');
        Route::patch('{news}/unpublish', [NewsController::class, 'unpublish'])->name('unpublish');
        Route::patch('{news}/pin', [NewsController::class, 'togglePin'])->name('pin');

        Route::get('/', [NewsController::class, 'index'])->name('index');
        Route::get('create', [NewsController::class, 'create'])->name('create');
        Route::post('/', [NewsController::class, 'store'])->name('store');
        Route::get('{news}/edit', [NewsController::class, 'edit'])->name('edit');
        Route::put('{news}', [NewsController::class, 'update'])->name('update');
        Route::delete('{news}', [NewsController::class, 'destroy'])->name('destroy');
    });
});
