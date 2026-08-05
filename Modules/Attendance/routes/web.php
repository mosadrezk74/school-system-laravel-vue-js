<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;

Route::middleware(['web', 'auth'])->prefix('attendance')->group(function () {
    Route::get('records', [AttendanceController::class, 'index'])->name('attendance.records.index');
    Route::get('records/create', [AttendanceController::class, 'create'])->name('attendance.records.create');
    Route::post('records', [AttendanceController::class, 'store'])->name('attendance.records.store');
    Route::get('records/{record}', [AttendanceController::class, 'show'])->name('attendance.records.show');
    Route::get('records/{record}/edit', [AttendanceController::class, 'edit'])->name('attendance.records.edit');
    Route::put('records/{record}', [AttendanceController::class, 'update'])->name('attendance.records.update');
    Route::delete('records/{record}', [AttendanceController::class, 'destroy'])->name('attendance.records.destroy');
});
