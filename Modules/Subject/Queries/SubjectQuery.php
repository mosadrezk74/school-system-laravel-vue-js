<?php

namespace Modules\Subject\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Subject\Models\Subject;

class SubjectQuery extends BaseQuery
{
    protected array $searchableFields = ['code', 'short_name'];

    protected array $filterableFields = ['branch_id', 'subject_type', 'is_active'];

    protected array $sortableFields = ['code', 'short_name', 'subject_type', 'credit_hours', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Subject::query()->with(['branch']);
    }

    public function search(?string $term): self
    {
        if (!$term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }

            $query->orWhere('name->en', 'LIKE', "%{$term}%")
                ->orWhere('name->ar', 'LIKE', "%{$term}%");
        });

        return $this;
    }

    public function branchId(?int $branchId): self
    {
        if ($branchId) {
            $this->query->where('branch_id', $branchId);
        }

        return $this;
    }

    public function subjectType(?string $subjectType): self
    {
        if ($subjectType) {
            $this->query->where('subject_type', $subjectType);
        }

        return $this;
    }

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }
}
