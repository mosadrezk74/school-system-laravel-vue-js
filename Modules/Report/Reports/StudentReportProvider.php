<?php

namespace Modules\Report\Reports;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Services\AcademicContextService;
use Modules\Report\DTOs\ReportFilterDTO;
use Modules\Report\DTOs\ReportResultDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Enums\ReportColumnType;
use Modules\Report\Enums\ReportFilterType;
use Modules\Student\Models\StudentProfile;

class StudentReportProvider extends AbstractReportProvider
{
    public function code(): string
    {
        return 'student.roster';
    }

    public function category(): string
    {
        return ReportCategory::Student->value;
    }

    public function filters(): array
    {
        return [
            $this->filter('class_id', ReportFilterType::Select, options: $this->classOptions()),
            $this->filter('is_active', ReportFilterType::Boolean),
            $this->filter('gender', ReportFilterType::Select, options: [
                ['value' => 'male', 'label' => __('report::report.options.male')],
                ['value' => 'female', 'label' => __('report::report.options.female')],
            ]),
        ];
    }

    public function columns(): array
    {
        return [
            $this->column('student_name'),
            $this->column('admission_number'),
            $this->column('class_name'),
            $this->column('gender'),
            $this->column('admission_date', ReportColumnType::Date),
            $this->column('status', ReportColumnType::Badge),
        ];
    }

    public function generate(ReportFilterDTO $filters): ReportResultDTO
    {
        $academicYearId = AcademicContextService::yearId();

        $students = StudentProfile::query()
            ->with(['user', 'currentClass'])
            ->when($academicYearId, fn (Builder $q) => $q->where('academic_year_id', $academicYearId))
            ->when($filters->int('class_id'), fn (Builder $q, $id) => $q->where('current_class_id', $id))
            ->when($filters->boolean('is_active') !== null, fn (Builder $q) => $q->where('is_active', $filters->boolean('is_active')))
            ->when($filters->string('gender'), fn (Builder $q, $gender) => $q->whereHas(
                'user',
                fn (Builder $user) => $user->where('gender', $gender),
            ))
            ->get();

        $rows = $students
            ->map(fn (StudentProfile $student): array => [
                'student_name' => $student->user?->name,
                'admission_number' => $student->admission_number,
                'class_name' => $student->currentClass?->name,
                'gender' => $student->user?->gender,
                'admission_date' => $student->admission_date?->toDateString(),
                'status' => $student->is_active
                    ? __('report::report.options.active')
                    : __('report::report.options.inactive'),
            ])
            ->sortBy('student_name')
            ->values()
            ->all();

        return new ReportResultDTO(
            rows: $rows,
            columns: $this->columns(),
            summary: [
                'total' => $students->count(),
                'active' => $students->where('is_active', true)->count(),
                'inactive' => $students->where('is_active', false)->count(),
            ],
            metadata: [
                'code' => $this->code(),
                'academic_year_id' => $academicYearId,
                'filters' => $filters->toArray(),
            ],
        );
    }
}
