<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Enums\ErrorReportStatus;
use Modules\Core\Models\ErrorReport;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TextFilter;

class ErrorReportQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['message', 'exception', 'route', 'error_code'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'last_seen_at',
        'first_seen_at',
        'occurrences',
        'status',
        'level',
        'created_at',
    ];

    protected string $defaultSortField = 'last_seen_at';

    protected function baseQuery(): Builder
    {
        return ErrorReport::query()->with(['user', 'resolver']);
    }

    /**
     * `trace`, `context`, `user_agent` and `ip_address` stay out. The first two
     * are large blobs no one narrows a list by; the last two identify the
     * reporter rather than the fault, and turning a crash log into a
     * per-person query tool is not what it is for.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'status' => EnumFilter::make(ErrorReportStatus::class)->label('systemErrors.status'),
            'level' => TextFilter::make()->label('systemErrors.level'),
            'exception' => TextFilter::make()->label('systemErrors.exception'),
            'route' => TextFilter::make()->label('systemErrors.route'),
            'method' => TextFilter::make()->label('systemErrors.method'),
            'status_code' => NumericFilter::make()->label('systemErrors.statusCode'),
            'occurrences' => NumericFilter::make()->label('systemErrors.occurrences'),
            'resolved_by' => ExactFilter::make()->label('systemErrors.resolvedBy')->nullable(),
            'last_seen' => DateRangeFilter::make()->column('last_seen_at')->label('systemErrors.lastSeen'),
            'first_seen' => DateRangeFilter::make()->column('first_seen_at')->label('systemErrors.firstSeen'),
        ];
    }
}
