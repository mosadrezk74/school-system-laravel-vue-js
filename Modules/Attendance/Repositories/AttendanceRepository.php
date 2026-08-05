<?php

namespace Modules\Attendance\Repositories;

use Illuminate\Support\Collection;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Models\AttendanceSummary;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function findRecordById(int $id): ?AttendanceRecord
    {
        return AttendanceRecord::query()->find($id);
    }

    public function findRecordByIdOrFail(int $id): AttendanceRecord
    {
        return AttendanceRecord::query()->findOrFail($id);
    }

    public function findRecordByClassDateSession(int $classId, string $date, int $session): ?AttendanceRecord
    {
        return AttendanceRecord::query()
            ->where('class_id', $classId)
            ->where('date', $date)
            ->where('session', $session)
            ->first();
    }

    public function createRecord(array $attributes): AttendanceRecord
    {
        return AttendanceRecord::create($attributes);
    }

    public function updateRecord(AttendanceRecord $record, array $attributes): AttendanceRecord
    {
        $record->update($attributes);

        return $record->refresh();
    }

    public function syncDetails(AttendanceRecord $record, array $detailRows): void
    {
        $studentIds = array_column($detailRows, 'student_id');

        AttendanceDetail::query()
            ->where('attendance_record_id', $record->id)
            ->when($studentIds, fn ($query) => $query->whereNotIn('student_id', $studentIds), fn ($query) => $query)
            ->delete();

        foreach ($detailRows as $row) {
            AttendanceDetail::updateOrCreate(
                ['attendance_record_id' => $record->id, 'student_id' => $row['student_id']],
                [
                    'status' => $row['status'],
                    'check_in_time' => $row['check_in_time'] ?? null,
                    'check_out_time' => $row['check_out_time'] ?? null,
                ],
            );
        }
    }

    public function detailsForRecord(int $recordId): Collection
    {
        return AttendanceDetail::query()
            ->where('attendance_record_id', $recordId)
            ->with('student.user')
            ->get();
    }

    public function deleteRecord(AttendanceRecord $record): bool
    {
        return (bool) $record->delete();
    }

    public function detailsForStudentInClassYear(int $studentId, int $classId, int $academicYearId): Collection
    {
        return AttendanceDetail::query()
            ->where('student_id', $studentId)
            ->whereHas('attendanceRecord', function ($query) use ($classId, $academicYearId) {
                $query->where('class_id', $classId)->where('academic_year_id', $academicYearId);
            })
            ->get();
    }

    public function upsertSummary(int $studentId, int $academicYearId, ?int $semesterId, int $classId, array $attributes): AttendanceSummary
    {
        return AttendanceSummary::updateOrCreate(
            ['student_id' => $studentId, 'academic_year_id' => $academicYearId, 'class_id' => $classId],
            [...$attributes, 'semester_id' => $semesterId],
        );
    }
}
