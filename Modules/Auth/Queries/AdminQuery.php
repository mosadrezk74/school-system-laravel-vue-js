<?php

namespace Modules\Auth\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseQuery;

/**
 * Administrative accounts only.
 *
 * "Admin" is not a profile table — it is a user whose account type is owner
 * or admin, so this query is the boundary that keeps the screen from turning
 * into a general user browser that lists every student in the school.
 */
class AdminQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'email', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return User::query()->whereIn('role', [
            UserRole::Owner->value,
            UserRole::Admin->value,
        ]);
    }

    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term): void {
            foreach (['name', 'email', 'username', 'phone'] as $column) {
                $query->orWhere($column, 'LIKE', "%{$term}%");
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

    /** Narrows to one account type; anything else is ignored. */
    public function accountRole(?string $role): self
    {
        if (in_array($role, [UserRole::Owner->value, UserRole::Admin->value], true)) {
            $this->query->where('role', $role);
        }

        return $this;
    }

    public function withRoles(): self
    {
        $this->query->with('roles');

        return $this;
    }
}
