<?php

namespace Modules\Academic\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\Filter;

class AcademicYearQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return AcademicYear::query();
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_current' => BooleanFilter::make()->label('academicYears.current'),
            'is_active' => BooleanFilter::make()->label('academicYears.active'),
            'start_date' => DateRangeFilter::make()->label('academicYears.startDate'),
            'end_date' => DateRangeFilter::make()->label('academicYears.endDate'),
        ];
    }
}
