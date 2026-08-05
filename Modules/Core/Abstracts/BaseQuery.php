<?php

namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Constants\Pagination;
use Modules\Core\Traits\CacheableTrait;
use Modules\Core\Traits\HasPaginationTrait;

abstract class BaseQuery
{
    use CacheableTrait;
    use HasPaginationTrait;

    protected Builder $query;
    protected array $filters = [];
    protected array $searchableFields = [];
    protected array $filterableFields = [];
    protected array $sortableFields = ['created_at'];
    protected array $withRelations = [];
    protected int $cacheTtl = 3600;

    public function __construct()
    {
        $this->query = $this->baseQuery();
    }

    abstract protected function baseQuery(): Builder;

    public function with(array $relations): self
    {
        $this->withRelations = array_merge($this->withRelations, $relations);
        return $this;
    }

    public function where(array $filters): self
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    public function search(string $term): self
    {
        if (empty($this->searchableFields)) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function sort(string $field, string $direction = 'asc'): self
    {
        if (in_array($field, $this->sortableFields)) {
            $this->query->orderBy($field, $direction);
        }
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }

    public function get(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->applyFilters()
            ->with($this->withRelations)
            ->get();
    }
    /**
     * $perPage is clamped: it usually arrives straight from ?per_page= , and
     * an unbounded value would let a client pull an entire table in one call.
     */
    public function paginate(int $perPage = Pagination::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $this->applyFilters()
            ->with($this->withRelations)
            ->paginate($this->resolvePerPage($perPage));
    }

    public function first(): ?object
    {
        return $this->applyFilters()
            ->with($this->withRelations)
            ->first();
    }

    public function count(): int
    {
        return $this->applyFilters()->count();
    }

    public function exists(): bool
    {
        return $this->applyFilters()->exists();
    }

    public function pluck(string $field): array
    {
        return $this->applyFilters()->pluck($field)->toArray();
    }

    protected function applyFilters(): Builder
    {
        $query = clone $this->query;

        foreach ($this->filters as $field => $value) {
            if (in_array($field, $this->filterableFields)) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    protected function getCachePrefix(): string
    {
        return class_basename($this);
    }
}
