<?php

namespace Modules\Auth\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Auth\DTOs\CreateRoleDTO;
use Modules\Auth\DTOs\UpdateRoleDTO;
use Modules\Auth\Interfaces\Repositories\RoleRepositoryInterface;
use Modules\Auth\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function findById(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    public function findByIdOrFail(int $id): Role
    {
        return Role::query()->findOrFail($id);
    }

    /**
     * MySQL's default collation is case-insensitive, so this also catches
     * "Teacher" when "teacher" already exists — which is exactly what the
     * seeder's canonical-casing workaround exists to prevent.
     */
    public function existsByName(string $name, ?int $ignoreId = null): bool
    {
        return Role::query()
            ->where('name', $name)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function createFromDTO(CreateRoleDTO $dto, string $guard): Role
    {
        return Role::query()->create([
            ...$dto->toArray(),
            'guard_name' => $guard,
            'is_system' => false,
        ]);
    }

    public function updateFromDTO(Role $role, UpdateRoleDTO $dto): Role
    {
        if ($dto->name !== null) {
            $role->update(['name' => $dto->name]);
        }

        return $role->refresh();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }

    /**
     * Spatie's model_has_roles is a plain pivot with no Eloquent model, so
     * the count is read straight from the table.
     */
    public function assignedUsersCount(Role $role): int
    {
        return DB::table('model_has_roles')
            ->where('role_id', $role->getKey())
            ->count();
    }
}
