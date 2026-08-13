<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TextFilter;
use Spatie\Activitylog\Models\Activity;

class ActivityQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['description', 'log_name', 'event', 'subject_type'];

    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'log_name', 'event'];

    protected function baseQuery(): Builder
    {
        return Activity::query()->with(['subject', 'causer']);
    }

    /**
     * Deliberately no academic year or semester. `activity_log` records what
     * changed, who changed it and when — it has no academic-period column, and
     * attaching the current context here would label historical entries with a
     * year they were never filed under. The academic context belongs in the
     * response around this list, not in its WHERE clause.
     *
     * `properties` and `attribute_changes` are JSON diffs of the change itself;
     * they are read on a single entry, not narrowed across a list.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'log_name' => TextFilter::make()->label('systemActivity.logName'),
            'event' => TextFilter::make()->label('systemActivity.event'),
            'subject_type' => TextFilter::make()->label('systemActivity.subjectType'),
            'causer_id' => ExactFilter::make()->label('systemActivity.causer')->nullable(),
            'created_at' => DateRangeFilter::make()->label('systemActivity.date'),
        ];
    }

    /**
     * Narrows to one record's history. Both halves must be present — a bare
     * type would return every row of that model, which is not what a
     * "history of this thing" view means.
     */
    public function subject(?string $subjectType, ?int $subjectId): self
    {
        if ($subjectType && $subjectId) {
            $this->query
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId);
        }

        return $this;
    }
}
