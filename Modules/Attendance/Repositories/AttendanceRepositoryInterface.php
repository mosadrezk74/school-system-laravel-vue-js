<?php

namespace Modules\Attendance\Repositories;

use Illuminate\Support\Collection;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Models\AttendanceSummary;

interface AttendanceRepositoryInterface
{
    public function findRecordById(int $id): ?AttendanceRecord;

    public function findRecordByIdOrFail(int $id): AttendanceRecord;

    public function findRecordByClassDateSession(int $classId, string $date, int $session): ?AttendanceRecord;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createRecord(array $attributes): AttendanceRecord;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateRecord(AttendanceRecord $record, array $attributes): AttendanceRecord;

    /**
     * @param  array<int, array{student_id: int, status: string, check_in_time: ?string, check_out_time: ?string}>  $detailRows
     */
    public function syncDetails(AttendanceRecord $record, array $detailRows): void;

    public function detailsForRecord(int $recordId): Collection;

    public function deleteRecord(AttendanceRecord $record): bool;

    /**
     * All detail rows for the given student, scoped to attendance_records in
     * the given class + academic year, keyed by status — the raw material
     * for recomputing that student's AttendanceSummary row.
     *
     * @return Collection<int, AttendanceDetail>
     */
    public function detailsForStudentInClassYear(int $studentId, int $classId, int $academicYearId): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function upsertSummary(int $studentId, int $academicYearId, ?int $semesterId, int $classId, array $attributes): AttendanceSummary;
}
