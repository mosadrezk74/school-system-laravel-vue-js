<?php

namespace Modules\Report\Reports;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Services\AcademicYearService;
use Modules\Meeting\Models\Meeting;
use Modules\Report\DTOs\ReportFilterDTO;
use Modules\Report\DTOs\ReportResultDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Enums\ReportColumnType;
use Modules\Report\Enums\ReportFilterType;

class MeetingReportProvider extends AbstractReportProvider
{
    public function code(): string
    {
        return 'meeting.sessions';
    }

    public function category(): string
    {
        return ReportCategory::Meeting->value;
    }

    public function filters(): array
    {
        return [
            $this->filter('teacher_id', ReportFilterType::Select, options: $this->teacherOptions()),
            $this->filter('status', ReportFilterType::Select, options: $this->statusOptions()),
            $this->filter('date_from', ReportFilterType::Date),
            $this->filter('date_to', ReportFilterType::Date),
        ];
    }

    public function columns(): array
    {
        return [
            $this->column('title'),
            $this->column('teacher_name'),
            $this->column('class_name'),
            $this->column('scheduled_at', ReportColumnType::DateTime),
            $this->column('duration_minutes', ReportColumnType::Number),
            $this->column('status', ReportColumnType::Badge),
        ];
    }

    public function generate(ReportFilterDTO $filters): ReportResultDTO
    {
        $academicYearId = AcademicYearService::id();

        $meetings = Meeting::query()
            ->with(['teacher', 'schoolClass'])
            ->when($academicYearId, fn (Builder $q) => $q->where('academic_year_id', $academicYearId))
            ->when($filters->int('teacher_id'), fn (Builder $q, $id) => $q->where('teacher_id', $id))
            ->when($filters->string('status'), fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters->string('date_from'), fn (Builder $q, $date) => $q->whereDate('scheduled_at', '>=', $date))
            ->when($filters->string('date_to'), fn (Builder $q, $date) => $q->whereDate('scheduled_at', '<=', $date))
            ->orderByDesc('scheduled_at')
            ->get();

        $rows = $meetings
            ->map(fn (Meeting $meeting): array => [
                'title' => $meeting->title,
                'teacher_name' => $meeting->teacher?->name,
                'class_name' => $meeting->schoolClass?->name,
                'scheduled_at' => $meeting->scheduled_at?->toIso8601String(),
                'duration_minutes' => $meeting->duration_minutes,
                'status' => __("report::report.options.{$meeting->status}"),
            ])
            ->all();

        return new ReportResultDTO(
            rows: $rows,
            columns: $this->columns(),
            summary: [
                'meetings' => $meetings->count(),
                'total_minutes' => (int) $meetings->sum('duration_minutes'),
                'teachers' => $meetings->pluck('teacher_id')->unique()->filter()->count(),
            ],
            metadata: [
                'code' => $this->code(),
                'academic_year_id' => $academicYearId,
                'filters' => $filters->toArray(),
            ],
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return collect(['scheduled', 'live', 'ended', 'cancelled'])
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => __("report::report.options.{$status}"),
            ])
            ->all();
    }
}
