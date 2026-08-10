<?php

use Illuminate\Support\Facades\Route;
use Modules\Portal\Http\Controllers\ParentAttendanceController;
use Modules\Portal\Http\Controllers\ParentChildController;
use Modules\Portal\Http\Controllers\ParentDashboardController;
use Modules\Portal\Http\Controllers\ParentFeeController;
use Modules\Portal\Http\Controllers\ParentGradeController;
use Modules\Portal\Http\Controllers\ParentHomeworkController;
use Modules\Portal\Http\Controllers\ParentMaterialController;
use Modules\Portal\Http\Controllers\ParentMeetingController;
use Modules\Portal\Http\Controllers\ParentPaymentRequestController;
use Modules\Portal\Http\Controllers\StudentAttendanceController;
use Modules\Portal\Http\Controllers\StudentDashboardController;
use Modules\Portal\Http\Controllers\StudentGradeController;
use Modules\Portal\Http\Controllers\StudentHomeworkController;
use Modules\Portal\Http\Controllers\StudentMaterialController;
use Modules\Portal\Http\Controllers\StudentMeetingController;
use Modules\Portal\Http\Controllers\StudentScheduleController;
use Modules\Portal\Http\Controllers\TeacherAttendanceController;
use Modules\Portal\Http\Controllers\TeacherClassController;
use Modules\Portal\Http\Controllers\TeacherDashboardController;
use Modules\Portal\Http\Controllers\TeacherHomeworkController;
use Modules\Portal\Http\Controllers\TeacherMeetingController;
use Modules\Portal\Http\Controllers\TeacherScheduleController;
use Modules\Portal\Http\Controllers\TeacherStudyMaterialController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware('portal:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::get('schedule', [TeacherScheduleController::class, 'index'])->name('schedule');
        Route::get('classes', [TeacherClassController::class, 'index'])->name('classes');

        Route::middleware('module:Attendance')->group(function () {
            Route::get('attendance', [TeacherAttendanceController::class, 'index'])->name('attendance');
            Route::post('attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
        });

        Route::middleware('module:Assignment')->group(function () {
            Route::get('homework', [TeacherHomeworkController::class, 'index'])->name('homework');
            Route::get('homework/create', [TeacherHomeworkController::class, 'create'])->name('homework.create');
            Route::post('homework', [TeacherHomeworkController::class, 'store'])->name('homework.store');
            Route::get('homework/{assignment}/edit', [TeacherHomeworkController::class, 'edit'])->name('homework.edit');
            Route::put('homework/{assignment}', [TeacherHomeworkController::class, 'update'])->name('homework.update');
            Route::delete('homework/{assignment}', [TeacherHomeworkController::class, 'destroy'])->name('homework.destroy');
        });

        Route::middleware('module:StudyMaterial')->group(function () {
            Route::get('study-materials', [TeacherStudyMaterialController::class, 'index'])->name('study-materials');
            Route::get('study-materials/create', [TeacherStudyMaterialController::class, 'create'])->name('study-materials.create');
            Route::post('study-materials', [TeacherStudyMaterialController::class, 'store'])->name('study-materials.store');
            Route::get('study-materials/{studyResource}/edit', [TeacherStudyMaterialController::class, 'edit'])->name('study-materials.edit');
            Route::post('study-materials/{studyResource}', [TeacherStudyMaterialController::class, 'update'])->name('study-materials.update');
            Route::delete('study-materials/{studyResource}', [TeacherStudyMaterialController::class, 'destroy'])->name('study-materials.destroy');
            Route::get('study-materials/{studyResource}/download', [TeacherStudyMaterialController::class, 'download'])->name('study-materials.download');
        });

        Route::middleware('module:Meeting')->group(function () {
            Route::get('meetings', [TeacherMeetingController::class, 'index'])->name('meetings');
            Route::get('meetings/create', [TeacherMeetingController::class, 'create'])->name('meetings.create');
            Route::post('meetings', [TeacherMeetingController::class, 'store'])->name('meetings.store');
            Route::get('meetings/{meeting}/edit', [TeacherMeetingController::class, 'edit'])->name('meetings.edit');
            Route::put('meetings/{meeting}', [TeacherMeetingController::class, 'update'])->name('meetings.update');
            Route::patch('meetings/{meeting}/start', [TeacherMeetingController::class, 'start'])->name('meetings.start');
            Route::patch('meetings/{meeting}/cancel', [TeacherMeetingController::class, 'cancel'])->name('meetings.cancel');
        });
    });

    Route::middleware('portal:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::middleware('module:Timetable')
            ->get('schedule', [StudentScheduleController::class, 'index'])->name('schedule');

        Route::middleware('module:Assignment')->group(function () {
            Route::get('homework', [StudentHomeworkController::class, 'index'])->name('homework');
            Route::get('homework/{assignment}', [StudentHomeworkController::class, 'show'])->name('homework.show');
            Route::post('homework/{assignment}/submit', [StudentHomeworkController::class, 'submit'])->name('homework.submit');
        });

        Route::middleware('module:Examination')
            ->get('grades', [StudentGradeController::class, 'index'])->name('grades');

        Route::middleware('module:Attendance')
            ->get('attendance', [StudentAttendanceController::class, 'index'])->name('attendance');

        Route::middleware('module:Meeting')
            ->get('meetings', [StudentMeetingController::class, 'index'])->name('meetings');

        Route::middleware('module:StudyMaterial')->group(function () {
            Route::get('materials', [StudentMaterialController::class, 'index'])->name('materials');
            Route::get('materials/{studyResource}/download', [StudentMaterialController::class, 'download'])->name('materials.download');
        });
    });

    Route::middleware('portal:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/', [ParentDashboardController::class, 'index'])->name('dashboard');

        Route::middleware('module:Student')
            ->get('children', [ParentChildController::class, 'index'])->name('children');

        Route::middleware('module:Attendance')
            ->get('attendance', [ParentAttendanceController::class, 'index'])->name('attendance');

        Route::middleware('module:Assignment')
            ->get('homework', [ParentHomeworkController::class, 'index'])->name('homework');

        Route::middleware('module:Examination')
            ->get('grades', [ParentGradeController::class, 'index'])->name('grades');

        Route::middleware('module:Finance')->group(function () {
            Route::get('fees', [ParentFeeController::class, 'index'])->name('fees');
            Route::get('payments', [ParentFeeController::class, 'payments'])->name('payments');

            Route::get('invoices/{invoice}/pay', [ParentPaymentRequestController::class, 'create'])
                ->name('invoices.pay');
            Route::post('payment-requests', [ParentPaymentRequestController::class, 'store'])
                ->name('payment-requests.store');
            Route::get('payment-requests/{payment}', [ParentPaymentRequestController::class, 'show'])
                ->name('payment-requests.show');
        });

        Route::middleware('module:Meeting')
            ->get('meetings', [ParentMeetingController::class, 'index'])->name('meetings');

        Route::middleware('module:StudyMaterial')->group(function () {
            Route::get('materials', [ParentMaterialController::class, 'index'])->name('materials');
            Route::get('materials/{studyResource}/download', [ParentMaterialController::class, 'download'])->name('materials.download');
        });
    });
});
