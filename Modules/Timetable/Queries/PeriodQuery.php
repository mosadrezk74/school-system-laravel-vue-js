<?php

namespace Modules\Timetable\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Timetable\Models\Period;

class PeriodQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['is_active', 'is_break'];

    /** @var array<int, string> */
    protected array $sortableFields = ['period_number', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Period::query()->orderBy('period_number');
    }
}
