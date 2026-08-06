<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;

Route::middleware(['web', 'auth'])->prefix('attendance')->group(function () {
    Route::get('dashboard', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');

    Route::get('students/{student}', [AttendanceController::class, 'studentHistory'])->name('attendance.students.show');

    Route::get('report', [AttendanceController::class, 'reportForm'])->name('attendance.report');
    Route::post('report', [AttendanceController::class, 'report'])->name('attendance.report.generate');

    Route::get('take', [AttendanceController::class, 'take'])->name('attendance.take');
    Route::post('take', [AttendanceController::class, 'storeTaken'])->name('attendance.take.store');

    Route::get('records', [AttendanceController::class, 'index'])->name('attendance.records.index');
    Route::get('records/create', [AttendanceController::class, 'create'])->name('attendance.records.create');
    Route::post('records', [AttendanceController::class, 'store'])->name('attendance.records.store');
    Route::get('records/{record}', [AttendanceController::class, 'show'])->name('attendance.records.show');
    Route::get('records/{record}/edit', [AttendanceController::class, 'edit'])->name('attendance.records.edit');
    Route::put('records/{record}', [AttendanceController::class, 'update'])->name('attendance.records.update');
    Route::delete('records/{record}', [AttendanceController::class, 'destroy'])->name('attendance.records.destroy');
});
