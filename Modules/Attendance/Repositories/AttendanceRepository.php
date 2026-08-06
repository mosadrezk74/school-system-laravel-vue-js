<?php

namespace Modules\Attendance\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Attendance\DTOs\AttendanceDashboardFilterDTO;
use Modules\Attendance\DTOs\AttendanceReportFilterDTO;
use Modules\Attendance\DTOs\StudentAttendanceFilterDTO;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Models\AttendanceSummary;
use Modules\Student\Models\StudentProfile;

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
                    'notes' => $row['notes'] ?? null,
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

    public function statusCountsByStudent(AttendanceReportFilterDTO $filter): Collection
    {
        return AttendanceDetail::query()
            ->join('attendance_records', 'attendance_records.id', '=', 'attendance_details.attendance_record_id')
            ->where('attendance_records.class_id', $filter->classId)
            ->when($filter->academicYearId, fn ($query, $year) => $query->where('attendance_records.academic_year_id', $year))
            ->when($filter->semesterId, fn ($query, $semester) => $query->where('attendance_records.semester_id', $semester))
            ->when($filter->dateFrom, fn ($query, $from) => $query->whereDate('attendance_records.date', '>=', $from))
            ->when($filter->dateTo, fn ($query, $to) => $query->whereDate('attendance_records.date', '<=', $to))
            ->groupBy('attendance_details.student_id', 'attendance_details.status')
            ->select([
                'attendance_details.student_id',
                'attendance_details.status',
                DB::raw('COUNT(*) as total'),
            ])
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $rows): array => $rows
                ->mapWithKeys(fn ($row): array => [(string) $row->status?->value => (int) $row->total])
                ->all());
    }

    public function studentsForReport(AttendanceReportFilterDTO $filter): Collection
    {
        return StudentProfile::query()
            ->where('current_class_id', $filter->classId)
            ->when($filter->academicYearId, fn ($query, $year) => $query->where('academic_year_id', $year))
            ->with('user:id,name')
            ->orderBy('id')
            ->get(['id', 'user_id', 'admission_number']);
    }

    public function statusCountsBetween(AttendanceDashboardFilterDTO $filter, string $from, string $to): array
    {
        return $this->dashboardDetailQuery($filter, $from, $to)
            ->groupBy('attendance_details.status')
            ->select(['attendance_details.status', DB::raw('COUNT(*) as total')])
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($total, $status): array => [(string) $status => (int) $total])
            ->all();
    }

    public function dailyStatusTotals(AttendanceDashboardFilterDTO $filter, string $from, string $to): Collection
    {
        return $this->dashboardDetailQuery($filter, $from, $to)
            ->groupBy('attendance_records.date')
            ->orderBy('attendance_records.date')
            ->select([
                'attendance_records.date',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN attendance_details.status = 'present' THEN 1 ELSE 0 END) as present"),
            ])
            ->get();
    }

    public function statusCountsForStudent(StudentAttendanceFilterDTO $filter): array
    {
        return $this->studentDetailQuery($filter)
            ->groupBy('attendance_details.status')
            ->select(['attendance_details.status', DB::raw('COUNT(*) as total')])
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($total, $status): array => [(string) $status => (int) $total])
            ->all();
    }

    public function timelineForStudent(StudentAttendanceFilterDTO $filter): Collection
    {
        return AttendanceDetail::query()
            ->where('attendance_details.student_id', $filter->studentId)
            ->whereHas('attendanceRecord', function ($query) use ($filter): void {
                $query
                    ->when($filter->academicYearId, fn ($inner, $year) => $inner->where('academic_year_id', $year))
                    ->when($filter->semesterId, fn ($inner, $semester) => $inner->where('semester_id', $semester))
                    ->when($filter->dateFrom, fn ($inner, $from) => $inner->whereDate('date', '>=', $from))
                    ->when($filter->dateTo, fn ($inner, $to) => $inner->whereDate('date', '<=', $to));
            })
            ->with([
                'attendanceRecord:id,date,session,class_id,subject_id,created_by',
                'attendanceRecord.createdBy:id,name',
                'attendanceRecord.subject:id,name',
            ])
            ->join('attendance_records', 'attendance_records.id', '=', 'attendance_details.attendance_record_id')
            ->orderByDesc('attendance_records.date')
            ->orderByDesc('attendance_records.session')
            ->select('attendance_details.*')
            ->get();
    }

    protected function dashboardDetailQuery(AttendanceDashboardFilterDTO $filter, string $from, string $to): Builder
    {
        return AttendanceDetail::query()
            ->join('attendance_records', 'attendance_records.id', '=', 'attendance_details.attendance_record_id')
            ->when($filter->classId, fn ($query, $class) => $query->where('attendance_records.class_id', $class))
            ->when($filter->academicYearId, fn ($query, $year) => $query->where('attendance_records.academic_year_id', $year))
            ->when($filter->semesterId, fn ($query, $semester) => $query->where('attendance_records.semester_id', $semester))
            ->whereDate('attendance_records.date', '>=', $from)
            ->whereDate('attendance_records.date', '<=', $to);
    }

    protected function studentDetailQuery(StudentAttendanceFilterDTO $filter): Builder
    {
        return AttendanceDetail::query()
            ->join('attendance_records', 'attendance_records.id', '=', 'attendance_details.attendance_record_id')
            ->where('attendance_details.student_id', $filter->studentId)
            ->when($filter->academicYearId, fn ($query, $year) => $query->where('attendance_records.academic_year_id', $year))
            ->when($filter->semesterId, fn ($query, $semester) => $query->where('attendance_records.semester_id', $semester))
            ->when($filter->dateFrom, fn ($query, $from) => $query->whereDate('attendance_records.date', '>=', $from))
            ->when($filter->dateTo, fn ($query, $to) => $query->whereDate('attendance_records.date', '<=', $to));
    }

    public function upsertSummary(int $studentId, int $academicYearId, ?int $semesterId, int $classId, array $attributes): AttendanceSummary
    {
        return AttendanceSummary::updateOrCreate(
            ['student_id' => $studentId, 'academic_year_id' => $academicYearId, 'class_id' => $classId],
            [...$attributes, 'semester_id' => $semesterId],
        );
    }
}
