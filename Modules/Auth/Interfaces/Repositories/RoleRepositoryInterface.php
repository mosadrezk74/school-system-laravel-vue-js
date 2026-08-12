<?php

namespace Modules\Auth\Interfaces\Repositories;

use Modules\Auth\DTOs\CreateRoleDTO;
use Modules\Auth\DTOs\UpdateRoleDTO;
use Modules\Auth\Models\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    public function findByIdOrFail(int $id): Role;

    public function existsByName(string $name, ?int $ignoreId = null): bool;

    public function createFromDTO(CreateRoleDTO $dto, string $guard): Role;

    public function updateFromDTO(Role $role, UpdateRoleDTO $dto): Role;

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissions(Role $role, array $permissions): Role;

    public function delete(Role $role): bool;

    public function assignedUsersCount(Role $role): int;
}
