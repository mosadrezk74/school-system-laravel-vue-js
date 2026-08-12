<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Interfaces\Repositories\RoleRepositoryInterface;
use Modules\Auth\Models\Role;
use Modules\Auth\Services\RoleGuardService;
use Modules\Core\Abstracts\BaseAction;
use Spatie\Permission\PermissionRegistrar;

class DeleteRoleAction extends BaseAction
{
    public function __construct(
        protected RoleRepositoryInterface $repository,
        protected RoleGuardService $guard,
    ) {}

    /**
     * Roles are hard-deleted: the table has no deleted_at, and Spatie
     * resolves roles by name, so a soft-deleted row would still collide with
     * a replacement of the same name.
     *
     * @param  Role  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Role $role */
        $role = $input;

        $deleted = $this->executeWithTransaction(function () use ($role): bool {
            $this->guard->assertNotOwnerRole($role);
            $this->guard->assertDeletable($role, $this->repository->assignedUsersCount($role));

            return $this->repository->delete($role);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $deleted;
    }
}
