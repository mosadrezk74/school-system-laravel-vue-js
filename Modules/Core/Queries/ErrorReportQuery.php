<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Models\ErrorReport;

class ErrorReportQuery extends BaseQuery
{
    protected array $searchableFields = ['message', 'exception', 'route', 'error_code'];

    protected array $filterableFields = ['status', 'level'];

    protected array $sortableFields = ['last_seen_at', 'first_seen_at', 'occurrences', 'status', 'level', 'created_at'];

    protected function baseQuery(): Builder
    {
        return ErrorReport::query()->with(['user', 'resolver']);
    }

    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function level(?string $level): self
    {
        if ($level) {
            $this->query->where('level', $level);
        }

        return $this;
    }
}
