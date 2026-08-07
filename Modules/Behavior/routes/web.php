<?php

use Illuminate\Support\Facades\Route;
use Modules\Behavior\Http\Controllers\BehaviorCategoryController;
use Modules\Behavior\Http\Controllers\BehaviorRecordController;

Route::middleware(['web', 'auth', 'module:Behavior'])->prefix('behavior')->group(function () {
    Route::resource('categories', BehaviorCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('records', BehaviorRecordController::class);
});
