<?php

namespace Modules\Attendance\Actions\Concerns;

trait RecomputesAttendanceSummary
{
    protected function recomputeSummary(int $studentId, int $academicYearId, ?int $semesterId, int $classId): void
    {
        $details = $this->repository->detailsForStudentInClassYear($studentId, $classId, $academicYearId);

        $totalDays = $details->pluck('attendance_record_id')->unique()->count();
        $presentDays = $details->where('status', 'present')->count();
        $absentDays = $details->where('status', 'absent')->count();
        $lateDays = $details->where('status', 'late')->count();

        $this->repository->upsertSummary($studentId, $academicYearId, $semesterId, $classId, [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'attendance_percentage' => $totalDays > 0 ? round($presentDays / $totalDays * 100, 2) : 0,
        ]);
    }
}
