<?php

namespace Modules\Timetable\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\RelationFilter;
use Modules\Timetable\Models\Timetable;

class TimetableQuery extends BaseQuery
{
    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'class' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Timetable::query()->with(['class.gradeLevel', 'academicYear', 'semester']);
    }

    /**
     * A timetable is a class's schedule for one academic period, so the year
     * and semester are its own columns and stay explicit historical filters.
     *
     * The grade level reaches through the class, since a timetable has no
     * grade level of its own.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'class_id' => ExactFilter::make()->label('timetables.class'),
            'academic_year_id' => ExactFilter::make()->label('timetables.academicYear'),
            'semester_id' => ExactFilter::make()->label('timetables.semester')->nullable(),
            'is_active' => BooleanFilter::make()->label('timetables.active'),
            'grade_level_id' => RelationFilter::make('class', 'grade_level_id')->label('timetables.gradeLevel'),
        ];
    }
}
