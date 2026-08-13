<?php

namespace Modules\Auth\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\Gender;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\RelationFilter;

class AdminQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name', 'email', 'username'];

    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'email', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return User::query()->whereIn('role', [
            UserRole::Owner->value,
            UserRole::Admin->value,
        ]);
    }

    /**
     * Authentication columns are deliberately absent. `password`,
     * `remember_token`, `two_factor_secret` and the rest are never filterable:
     * narrowing a user list by a credential column is a way to probe it, and
     * no administrator has a reason to.
     *
     * `national_id` and `birth_date` are also left out — they identify a
     * specific person rather than describing an account, and the search box
     * already covers looking someone up.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            // Restricted to the two staff roles by the base query, so the
            // enum's other cases simply match nothing.
            'account_role' => EnumFilter::make(UserRole::class)->column('role')->label('admins.accountRole'),

            'is_active' => BooleanFilter::make()->label('admins.active'),
            'gender' => EnumFilter::make(Gender::class)->label('admins.gender'),
            'role_id' => RelationFilter::make('roles', 'roles.id')->label('admins.role'),
        ];
    }

    public function withRoles(): self
    {
        $this->query->with('roles');

        return $this;
    }
}
