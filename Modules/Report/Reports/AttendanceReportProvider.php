<?php

namespace Modules\Report\Reports;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Services\AcademicYearService;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Report\DTOs\ReportFilterDTO;
use Modules\Report\DTOs\ReportResultDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Enums\ReportColumnType;
use Modules\Report\Enums\ReportFilterType;

class AttendanceReportProvider extends AbstractReportProvider
{
    public function code(): string
    {
        return 'attendance.summary';
    }

    public function category(): string
    {
        return ReportCategory::Attendance->value;
    }

    public function filters(): array
    {
        return [
            $this->filter('class_id', ReportFilterType::Select, options: $this->classOptions()),
            $this->filter('date_from', ReportFilterType::Date),
            $this->filter('date_to', ReportFilterType::Date),
        ];
    }

    public function columns(): array
    {
        return [
            $this->column('student_name'),
            $this->column('admission_number'),
            $this->column('present', ReportColumnType::Number),
            $this->column('absent', ReportColumnType::Number),
            $this->column('late', ReportColumnType::Number),
            $this->column('excused', ReportColumnType::Number),
            $this->column('total', ReportColumnType::Number),
            $this->column('percentage', ReportColumnType::Percentage),
        ];
    }

    public function generate(ReportFilterDTO $filters): ReportResultDTO
    {
        $academicYearId = AcademicYearService::id();

        $details = AttendanceDetail::query()
            ->with(['student.user'])
            ->whereHas('attendanceRecord', function (Builder $query) use ($filters, $academicYearId) {
                $query
                    ->when($academicYearId, fn (Builder $q) => $q->where('academic_year_id', $academicYearId))
                    ->when($filters->int('class_id'), fn (Builder $q, $id) => $q->where('class_id', $id))
                    ->when($filters->string('date_from'), fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                    ->when($filters->string('date_to'), fn (Builder $q, $date) => $q->whereDate('date', '<=', $date));
            })
            ->get();

        $rows = $details
            ->groupBy('student_id')
            ->map(function ($group) {
                $counts = $group->countBy(fn (AttendanceDetail $detail): string => $detail->status->value);
                $student = $group->first()->student;

                $present = $counts[AttendanceStatus::Present->value] ?? 0;
                $late = $counts[AttendanceStatus::Late->value] ?? 0;
                $total = $group->count();

                return [
                    'student_name' => $student?->user?->name,
                    'admission_number' => $student?->admission_number,
                    'present' => $present,
                    'absent' => $counts[AttendanceStatus::Absent->value] ?? 0,
                    'late' => $late,
                    'excused' => $counts[AttendanceStatus::Excused->value] ?? 0,
                    'total' => $total,
                    'percentage' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0,
                ];
            })
            ->sortBy('student_name')
            ->values()
            ->all();

        return new ReportResultDTO(
            rows: $rows,
            columns: $this->columns(),
            summary: [
                'students' => count($rows),
                'records' => $details->count(),
                'average_percentage' => $rows === []
                    ? 0
                    : round(collect($rows)->avg('percentage'), 2),
            ],
            metadata: [
                'code' => $this->code(),
                'academic_year_id' => $academicYearId,
                'filters' => $filters->toArray(),
            ],
        );
    }
}
