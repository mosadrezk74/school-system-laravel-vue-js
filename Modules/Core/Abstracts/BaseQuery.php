<?php

namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Constants\Pagination;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Traits\CacheableTrait;
use Modules\Core\Traits\HasPaginationTrait;
use Modules\Core\Traits\HasSearchTrait;
use Modules\Core\Traits\HasSortingTrait;

/**
 * The list-query base: search, filtering, sorting and pagination for every
 * index page in the application.
 *
 * Filtering is declarative and closed. A Query names the fields it is willing
 * to be filtered on in `allowedFilters()`, each mapped to a typed Filter that
 * owns its own operators; `where()` then feeds raw request input through that
 * map and discards everything else. Nothing reads a column name, relation or
 * operator out of the request, so the URL cannot reach a field the Query did
 * not open up.
 *
 *     request input  →  allowedFilters()  →  typed Filter  →  Eloquent Builder
 *
 * Search and sorting are whitelisted the same way, through HasSearchTrait and
 * HasSortingTrait — which are the single implementation of both, not a second
 * copy alongside one here.
 */
abstract class BaseQuery
{
    use CacheableTrait;
    use HasPaginationTrait;
    use HasSearchTrait;
    use HasSortingTrait;

    /** @var Builder<Model> */
    protected Builder $query;

    /**
     * Raw flat request input, resolved against allowedFilters() on execute.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /** @var array<int, string> Plain columns global search matches. */
    protected array $searchableFields = [];

    /** @var array<int, string> Translatable JSON columns global search matches. */
    protected array $translatableSearchFields = [];

    /** @var array<string, array<int, string>> Related columns global search matches. */
    protected array $searchableRelations = [];

    /** @var array<int, string> */
    protected array $sortableFields = [Pagination::DEFAULT_SORT];

    protected string $defaultSortField = Pagination::DEFAULT_SORT;

    /** @var array<int, string> */
    protected array $withRelations = [];

    protected int $cacheTtl = 3600;

    public function __construct()
    {
        $this->query = $this->baseQuery();
    }

    /**
     * @return Builder<Model>
     */
    abstract protected function baseQuery(): Builder;

    /**
     * The fields this Query may be filtered on, keyed by their query-string
     * name. Anything absent from this map is unreachable from the request.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [];
    }

    /**
     * @param  array<int, string>  $relations
     */
    public function with(array $relations): self
    {
        $this->withRelations = array_merge($this->withRelations, $relations);

        return $this;
    }

    /**
     * Hand the Query raw flat request input — typically `$request->all()`.
     *
     * Passing everything is safe precisely because allowedFilters() is the
     * whitelist: unknown keys, `page`, `sort_by` and anything a client invents
     * are simply never looked up.
     *
     * @param  array<string, mixed>  $filters
     */
    public function where(array $filters): self
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    public function search(?string $term): self
    {
        $this->query = $this->applySearch($this->query, $term);

        return $this;
    }

    public function sort(?string $field = null, ?string $direction = null): self
    {
        $this->query = $this->applySorting($this->query, $field, $direction);

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit($limit);

        return $this;
    }

    /**
     * The metadata AppFilterBar renders this Query's controls from.
     *
     * Dynamic option lists — classes, subjects, academic years — are loaded by
     * the controller and merged in by key, so the Query stays free of data
     * access while the frontend still receives one explicit definition per
     * field.
     *
     * @param  array<string, array<int, array{title: string, value: mixed}>>  $options
     * @return array<int, array<string, mixed>>
     */
    public function filterSchema(array $options = []): array
    {
        $schema = [];

        foreach ($this->allowedFilters() as $key => $filter) {
            $definition = $filter->definition($key);

            if (array_key_exists($key, $options)) {
                $definition['options'] = $options[$key];
            }

            $schema[] = $definition;
        }

        return $schema;
    }

    /**
     * The subset of request input this Query actually recognises, in the shape
     * the index page needs to restore its controls from the URL.
     *
     * Echoing only recognised keys is what stops an arbitrary query string
     * from being reflected back into the page props.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function activeFilters(array $input): array
    {
        $active = [];

        foreach (array_keys($this->allowedFilters()) as $key) {
            foreach ([$key, "{$key}_op", "{$key}_from", "{$key}_to"] as $candidate) {
                if (isset($input[$candidate]) && $input[$candidate] !== '') {
                    $active[$candidate] = $input[$candidate];
                }
            }
        }

        return $active;
    }

    /**
     * @return Collection<int, Model>
     */
    public function get(): Collection
    {
        return $this->applyFilters()
            ->with($this->withRelations)
            ->get();
    }

    /**
     * $perPage is clamped: it usually arrives straight from ?per_page= , and
     * an unbounded value would let a client pull an entire table in one call.
     *
     * @return LengthAwarePaginator<int, Model>
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

    /**
     * @return array<int, mixed>
     */
    public function pluck(string $field): array
    {
        return $this->applyFilters()->pluck($field)->toArray();
    }

    /**
     * Resolve the collected input through the filter map. Filters are ANDed;
     * each one that finds no value of its own is a no-op.
     *
     * @return Builder<Model>
     */
    protected function applyFilters(): Builder
    {
        $query = clone $this->query;

        foreach ($this->allowedFilters() as $key => $filter) {
            $filter->applyTo($query, $this->filters, $key);
        }

        return $query;
    }

    protected function getCachePrefix(): string
    {
        return class_basename($this);
    }
}
