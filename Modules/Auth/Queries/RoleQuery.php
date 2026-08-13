<?php

namespace Modules\Auth\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Models\Role;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TextFilter;

class RoleQuery extends BaseQuery
{
    /** Role names are plain strings, not translatable JSON. */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'is_system', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Role::query();
    }

    /**
     * `guard_name` is left out: the application uses a single guard, so the
     * column would filter nothing while exposing an auth internal.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_system' => BooleanFilter::make()->label('roles.filterSystem'),
            'name' => TextFilter::make()->label('roles.name'),
        ];
    }

    public function withCounts(): self
    {
        $this->query->withCount(['permissions', 'users']);

        return $this;
    }
}
