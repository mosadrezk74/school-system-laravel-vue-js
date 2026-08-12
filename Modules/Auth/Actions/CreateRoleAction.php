<?php

namespace Modules\Auth\Actions;

use Modules\Auth\DTOs\CreateRoleDTO;
use Modules\Auth\Exceptions\RoleOperationException;
use Modules\Auth\Interfaces\Repositories\RoleRepositoryInterface;
use Modules\Auth\Models\Role;
use Modules\Auth\Services\RoleGuardService;
use Modules\Core\Abstracts\BaseAction;
use Spatie\Permission\PermissionRegistrar;

class CreateRoleAction extends BaseAction
{
    public function __construct(
        protected RoleRepositoryInterface $repository,
        protected RoleGuardService $guard,
    ) {}

    /**
     * @param  CreateRoleDTO  $input
     */
    public function execute(mixed $input): Role
    {
        /** @var CreateRoleDTO $dto */
        $dto = $input;

        $guardName = config('auth.defaults.guard', 'web');

        $role = $this->executeWithTransaction(function () use ($dto, $guardName): Role {
            if ($this->repository->existsByName($dto->name)) {
                throw RoleOperationException::nameAlreadyExists();
            }

            $permissions = $this->guard->resolveExistingPermissions($dto->permissions, $guardName);

            $this->guard->assertMayGrant($permissions);

            $role = $this->repository->createFromDTO($dto, $guardName);

            return $this->repository->syncPermissions($role, $permissions);
        });

        // Spatie caches the whole permission map; without this the new role
        // resolves as having no abilities until the cache expires.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }
}
