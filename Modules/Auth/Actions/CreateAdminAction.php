<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Modules\Auth\DTOs\CreateAdminDTO;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Exceptions\AdminOperationException;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;
use Modules\Auth\Services\AdminGuardService;
use Modules\Core\Abstracts\BaseAction;
use Spatie\Permission\PermissionRegistrar;

class CreateAdminAction extends BaseAction
{
    public function __construct(
        protected AdminRepositoryInterface $repository,
        protected AdminGuardService $guard,
    ) {}

    /**
     * @param  CreateAdminDTO  $input
     */
    public function execute(mixed $input): User
    {
        /** @var CreateAdminDTO $dto */
        $dto = $input;

        $admin = $this->executeWithTransaction(function () use ($dto): User {
            if ($this->repository->existsByEmail($dto->email)) {
                throw AdminOperationException::emailAlreadyExists();
            }

            $accountRole = $this->guard->resolveAccountRole($dto->accountRole, UserRole::Admin->value);

            /*
             * The Spatie role defaults to the account type so a new admin is
             * never created with abilities but no role, or vice versa.
             */
            $roles = $dto->roles !== [] ? $dto->roles : [$accountRole];

            $this->guard->assertMayAssignRoles($roles);

            $admin = $this->repository->create($dto, $accountRole);

            return $this->repository->syncRoles($admin, $roles);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $admin;
    }
}
