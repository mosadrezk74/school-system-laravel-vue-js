<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Examination\Enums\ExamType;
use Modules\Examination\Models\Exam;

class ExamQuery extends BaseQuery
{
    /**
     * `name` became a translatable JSON column.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['start_date', 'end_date', 'exam_type', 'is_published', 'created_at'];

    protected string $defaultSortField = 'start_date';

    protected function baseQuery(): Builder
    {
        return Exam::query()->with(['academicYear', 'semester', 'class']);
    }

    /**
     * `weightage` is left out: it is a scoring coefficient used when a report
     * card is computed, not something anyone browses the exam list by.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'academic_year_id' => ExactFilter::make()->label('exams.academicYear'),
            'semester_id' => ExactFilter::make()->label('exams.semester')->nullable(),
            'class_id' => ExactFilter::make()->label('exams.class')->nullable(),
            'exam_type' => EnumFilter::make(ExamType::class)->label('exams.type'),
            'is_published' => BooleanFilter::make()->label('exams.published'),
            'name' => TranslatableFilter::make()->label('exams.name'),
            'start_date' => DateRangeFilter::make()->label('exams.startDate'),
            'end_date' => DateRangeFilter::make()->label('exams.endDate'),
            'total_marks' => NumericFilter::make()->label('exams.totalMarks'),
        ];
    }
}
