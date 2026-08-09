<?php

namespace Modules\Academic\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseQuery;

class AcademicYearQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['is_current', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return AcademicYear::query();
    }
}
