<?php

namespace Modules\Report\Reports;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\GradeLevel;
use Modules\Report\DTOs\ReportFilterDTO;
use Modules\Report\DTOs\ReportResultDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Enums\ReportColumnType;
use Modules\Report\Enums\ReportFilterType;
use Modules\StudyMaterial\Enums\StudyResourceType;
use Modules\StudyMaterial\Models\StudyResource;

class StudyMaterialReportProvider extends AbstractReportProvider
{
    public function code(): string
    {
        return 'study_material.resources';
    }

    public function category(): string
    {
        return ReportCategory::Academic->value;
    }

    public function filters(): array
    {
        return [
            $this->filter('teacher_id', ReportFilterType::Select, options: $this->teacherOptions()),
            $this->filter('grade_level_id', ReportFilterType::Select, options: $this->gradeLevelOptions()),
            $this->filter('type', ReportFilterType::Select, options: StudyResourceType::options()),
            $this->filter('date_from', ReportFilterType::Date),
            $this->filter('date_to', ReportFilterType::Date),
        ];
    }

    public function columns(): array
    {
        return [
            $this->column('title'),
            $this->column('subject_name'),
            $this->column('grade_level_name'),
            $this->column('teacher_name'),
            $this->column('type', ReportColumnType::Badge),
            $this->column('downloads_count', ReportColumnType::Number),
            $this->column('created_at', ReportColumnType::Date),
        ];
    }

    public function generate(ReportFilterDTO $filters): ReportResultDTO
    {
        $resources = StudyResource::query()
            ->with(['subject', 'gradeLevel', 'teacher'])
            ->when($filters->int('teacher_id'), fn (Builder $q, $id) => $q->where('teacher_id', $id))
            ->when($filters->int('grade_level_id'), fn (Builder $q, $id) => $q->where('grade_level_id', $id))
            ->when($filters->string('type'), fn (Builder $q, $type) => $q->where('type', $type))
            ->when($filters->string('date_from'), fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters->string('date_to'), fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->get();

        $rows = $resources
            ->map(fn (StudyResource $resource): array => [
                'title' => $resource->title,
                'subject_name' => $resource->subject?->name,
                'grade_level_name' => $resource->gradeLevel?->name,
                'teacher_name' => $resource->teacher?->name,
                'type' => $resource->type?->label(),
                'downloads_count' => $resource->downloads_count,
                'created_at' => $resource->created_at?->toDateString(),
            ])
            ->all();

        return new ReportResultDTO(
            rows: $rows,
            columns: $this->columns(),
            summary: [
                'resources' => $resources->count(),
                'total_downloads' => (int) $resources->sum('downloads_count'),
                'teachers' => $resources->pluck('teacher_id')->unique()->filter()->count(),
            ],
            metadata: [
                'code' => $this->code(),
                'filters' => $filters->toArray(),
            ],
        );
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function gradeLevelOptions(): array
    {
        return GradeLevel::query()
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->map(fn (GradeLevel $level): array => [
                'value' => $level->id,
                'label' => $level->name,
            ])
            ->all();
    }
}
