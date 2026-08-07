<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\FeeCategory;

class FeeCategoryQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['code', 'name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return FeeCategory::query();
    }

    public function searchTerm(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            $query->where('code', 'LIKE', "%{$term}%")
                ->orWhere('name', 'LIKE', "%{$term}%");
        });

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
