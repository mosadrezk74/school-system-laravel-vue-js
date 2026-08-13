<?php

namespace Modules\SchoolClass\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\RelationFilter;
use Modules\SchoolClass\Models\SchoolClass;

class ClassQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'name',
        'capacity',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return SchoolClass::query()->with(['gradeLevel', 'classroom']);
    }

    /**
     * No `academic_year_id`: the column was dropped from `classes` on purpose —
     * a class is year-independent, and enrolment carries the year instead.
     * Offering the filter here would imply a relationship the schema no longer
     * has.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'grade_level_id' => ExactFilter::make()->label('classes.gradeLevel'),
            'classroom_id' => ExactFilter::make()->label('classes.classroom')->nullable(),
            'is_active' => BooleanFilter::make()->label('classes.active'),
            'capacity' => NumericFilter::make()->label('classes.capacity'),
            'has_students' => RelationFilter::make('students')->label('classes.hasStudents'),
        ];
    }
}
