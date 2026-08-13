<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Examination\Models\ExamGrade;

class ExamGradeQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['grade_name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['grade_name', 'grade_point', 'min_percentage', 'created_at'];

    protected string $defaultSortField = 'min_percentage';

    protected function baseQuery(): Builder
    {
        return ExamGrade::query()->with(['gradeLevel', 'subject']);
    }

    /**
     * A grade band is scoped to a grade level and optionally to one subject —
     * a null subject being the level's default scale, which is why the subject
     * filter is nullable.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'grade_level_id' => ExactFilter::make()->label('examGrades.gradeLevel'),
            'subject_id' => ExactFilter::make()->label('examGrades.subject')->nullable(),
            'is_passing' => BooleanFilter::make()->label('examGrades.isPassing'),
            'grade_name' => TextFilter::make()->label('examGrades.gradeName'),
            'grade_point' => NumericFilter::make()->label('examGrades.gradePoint'),
            'min_percentage' => NumericFilter::make()->label('examGrades.minPercentage'),
        ];
    }
}
