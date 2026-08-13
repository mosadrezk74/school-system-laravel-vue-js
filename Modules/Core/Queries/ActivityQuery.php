<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Models\Activity;

class ActivityQuery extends BaseQuery
{
    protected array $searchableFields = ['description', 'log_name', 'event', 'subject_type'];

    protected array $filterableFields = ['log_name', 'event', 'subject_type', 'causer_id'];

    protected array $sortableFields = ['created_at', 'log_name', 'event'];

    /**
     * Subject and causer are morphs, so they are eager-loaded to keep a page
     * of entries from firing a query per row.
     */
    protected function baseQuery(): Builder
    {
        return Activity::query()->with(['subject', 'causer']);
    }

    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term): void {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    /** One log channel, e.g. "default". */
    public function logName(?string $logName): self
    {
        if ($logName) {
            $this->query->where('log_name', $logName);
        }

        return $this;
    }

    /** One recorded verb — created, updated, deleted. */
    public function event(?string $event): self
    {
        if ($event) {
            $this->query->where('event', $event);
        }

        return $this;
    }

    /**
     * The model an entry is about. The id alone is meaningless without its
     * type, so it is only applied once a subject type is given.
     */
    public function subject(?string $subjectType, ?int $subjectId): self
    {
        if (! $subjectType) {
            return $this;
        }

        $this->query->where('subject_type', $subjectType);

        if ($subjectId !== null) {
            $this->query->where('subject_id', $subjectId);
        }

        return $this;
    }

    public function causer(?int $causerId): self
    {
        if ($causerId !== null) {
            $this->query->where('causer_id', $causerId);
        }

        return $this;
    }
}
