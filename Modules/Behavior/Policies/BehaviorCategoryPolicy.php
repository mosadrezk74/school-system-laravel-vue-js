<?php

namespace Modules\Behavior\Policies;

use App\Models\User;
use Modules\Behavior\Models\BehaviorCategory;

/**
 * RolePermissionSeeder only registers a `behavior-record` permission
 * resource ('Behavior' => ['behavior-record']) — there is no separate
 * `behavior-category` resource. Categories are just the point-scoring
 * config layer behind records, so this policy reuses the `behavior-record`
 * abilities rather than requesting a new permission resource.
 */
class BehaviorCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('behavior-record.view');
    }

    public function view(User $user, BehaviorCategory $category): bool
    {
        return $user->can('behavior-record.view');
    }

    public function create(User $user): bool
    {
        return $user->can('behavior-record.create');
    }

    public function update(User $user, BehaviorCategory $category): bool
    {
        return $user->can('behavior-record.update');
    }

    public function delete(User $user, BehaviorCategory $category): bool
    {
        return $user->can('behavior-record.delete');
    }

    public function restore(User $user, BehaviorCategory $category): bool
    {
        return $user->can('behavior-record.delete');
    }
}
