<?php

namespace Modules\Auth\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Models\Role;
use Modules\Core\Abstracts\BaseQuery;

class RoleQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'is_system', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Role::query();
    }

    /** Role names are plain strings, not translatable JSON. */
    public function search(?string $term): self
    {
        if ($term) {
            $this->query->where('name', 'LIKE', "%{$term}%");
        }

        return $this;
    }

    public function isSystem(?bool $isSystem): self
    {
        if ($isSystem !== null) {
            $this->query->where('is_system', $isSystem);
        }

        return $this;
    }

    /**
     * The index shows how many accounts hold each role so an admin can see
     * what a delete would affect before attempting it.
     */
    public function withCounts(): self
    {
        $this->query->withCount(['permissions', 'users']);

        return $this;
    }
}
