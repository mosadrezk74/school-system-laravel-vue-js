<?php

namespace Modules\Attendance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\RelationFilter;

class AttendanceQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['date', 'session', 'created_at'];

    protected string $defaultSortField = 'date';

    protected function baseQuery(): Builder
    {
        return AttendanceRecord::query()->with(['class', 'subject', 'academicYear', 'semester']);
    }

    /**
     * A record is one class's register for one day, so the counts are its
     * summary rather than its identity — but "which days had absences" is a
     * real question, which is why they are filterable as ranges.
     *
     * `student_id` reaches through `details`: a register row has no single
     * student, so finding the days a given student appears on means going
     * through the detail rows.
     *
     * No `subject_id` or `semester_id`. The model declares both relations, but
     * `attendance_records` has neither column — attendance is taken per class,
     * per day, per session. The old `subjectId()` / `semesterId()` methods
     * would have produced "unknown column" had anything ever passed them a
     * value, and are not carried forward.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'class_id' => ExactFilter::make()->label('attendanceRecords.class'),
            'academic_year_id' => ExactFilter::make()->label('attendanceRecords.academicYear'),
            'session' => NumericFilter::make()->label('attendanceRecords.session'),
            'date' => DateRangeFilter::make()->label('attendanceRecords.date'),
            'student_id' => RelationFilter::make('details', 'student_id')->label('attendanceRecords.student'),
            'absent_count' => NumericFilter::make()->label('attendanceRecords.absent'),
            'late_count' => NumericFilter::make()->label('attendanceRecords.late'),
        ];
    }
}
