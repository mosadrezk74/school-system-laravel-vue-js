<?php

namespace Modules\Report\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Report\Models\ReportTemplate;

class ReportTemplateQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name', 'code'];

    /** @var array<int, string> */
    protected array $filterableFields = ['category', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'category', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return ReportTemplate::query()->with('createdBy');
    }

    public function searchTerm(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('code', 'LIKE', "%{$term}%");
        });

        return $this;
    }

    public function category(?string $category): self
    {
        if ($category) {
            $this->query->where('category', $category);
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
