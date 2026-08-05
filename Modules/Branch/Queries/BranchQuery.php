<?php

namespace Modules\Branch\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseQuery;

class BranchQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name', 'code', 'city', 'address'];

    /** @var array<int, string> */
    protected array $filterableFields = ['is_active', 'is_main'];

    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'code', 'city', 'is_active', 'is_main', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Branch::query()->with(['manager']);
    }

    /**
     * Override the search method to handle null values
     */
    public function search(?string $term): self
    {
        if (empty($term)) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }
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

    public function isMain(?bool $isMain): self
    {
        if ($isMain !== null) {
            $this->query->where('is_main', $isMain);
        }

        return $this;
    }

    public function city(?string $city): self
    {
        if ($city) {
            $this->query->where('city', 'LIKE', "%{$city}%");
        }

        return $this;
    }
}