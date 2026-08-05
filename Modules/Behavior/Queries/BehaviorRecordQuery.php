<?php

namespace Modules\Behavior\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Core\Abstracts\BaseQuery;

class BehaviorRecordQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['date', 'points', 'created_at'];

    protected function baseQuery(): Builder
    {
        return BehaviorRecord::query()->with(['student.user', 'behaviorCategory', 'reportedBy', 'reviewedBy']);
    }

    public function studentId(?int $studentId): self
    {
        if ($studentId) {
            $this->query->where('student_id', $studentId);
        }

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function type(?string $type): self
    {
        if ($type) {
            $this->query->whereHas('behaviorCategory', function (Builder $query) use ($type) {
                $query->where('type', $type);
            });
        }

        return $this;
    }

    public function dateFrom(?string $dateFrom): self
    {
        if ($dateFrom) {
            $this->query->whereDate('date', '>=', $dateFrom);
        }

        return $this;
    }

    public function dateTo(?string $dateTo): self
    {
        if ($dateTo) {
            $this->query->whereDate('date', '<=', $dateTo);
        }

        return $this;
    }
}
