<?php

namespace Modules\Timetable\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Timetable\Models\Period;

class PeriodQuery extends BaseQuery
{
    /**
     * `name` is JSON keyed by locale, matched through each enabled language.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['period_number', 'start_time', 'is_active', 'created_at'];

    protected string $defaultSortField = 'period_number';

    protected function baseQuery(): Builder
    {
        return Period::query()->orderBy('period_number');
    }

    /**
     * `start_time` / `end_time` are not filterable: the whole list is a
     * handful of rows shown in order, so a time filter would be more work than
     * reading it.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_active' => BooleanFilter::make()->label('timetables.periodActive'),
            'is_break' => BooleanFilter::make()->label('timetables.isBreak'),
            'name' => TranslatableFilter::make()->label('timetables.periodName'),
            'period_number' => NumericFilter::make()->label('timetables.periodNumber'),
        ];
    }
}
