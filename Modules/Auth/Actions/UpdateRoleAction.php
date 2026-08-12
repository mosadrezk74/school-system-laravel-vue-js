<?php

namespace Modules\Auth\Actions;

use Modules\Auth\DTOs\UpdateRoleDTO;
use Modules\Auth\Exceptions\RoleOperationException;
use Modules\Auth\Interfaces\Repositories\RoleRepositoryInterface;
use Modules\Auth\Models\Role;
use Modules\Auth\Services\RoleGuardService;
use Modules\Core\Abstracts\BaseAction;
use Spatie\Permission\PermissionRegistrar;

class UpdateRoleAction extends BaseAction
{
    public function __construct(
        protected RoleRepositoryInterface $repository,
        protected RoleGuardService $guard,
    ) {}

    /**
     * @param  array{role: Role, dto: UpdateRoleDTO}  $input
     */
    public function execute(mixed $input): Role
    {
        /** @var Role $role */
        $role = $input['role'];

        /** @var UpdateRoleDTO $dto */
        $dto = $input['dto'];

        $guardName = $role->guard_name ?: config('auth.defaults.guard', 'web');

        $updated = $this->executeWithTransaction(function () use ($role, $dto, $guardName): Role {
            $this->guard->assertNotOwnerRole($role);
            $this->guard->assertRenameAllowed($role, $dto->name);

            if ($dto->name !== null
                && $dto->name !== $role->name
                && $this->repository->existsByName($dto->name, $role->getKey())
            ) {
                throw RoleOperationException::nameAlreadyExists();
            }

            $permissions = $this->guard->resolveExistingPermissions($dto->permissions, $guardName);

            $this->guard->assertMayGrant($permissions);
            $this->guard->assertNoSelfLockout($role, $permissions);

            $role = $this->repository->updateFromDTO($role, $dto);

            return $this->repository->syncPermissions($role, $permissions);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $updated;
    }
}
