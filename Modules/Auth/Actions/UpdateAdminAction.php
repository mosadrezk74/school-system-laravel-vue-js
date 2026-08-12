<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Modules\Auth\DTOs\UpdateAdminDTO;
use Modules\Auth\Exceptions\AdminOperationException;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;
use Modules\Auth\Services\AdminGuardService;
use Modules\Core\Abstracts\BaseAction;
use Spatie\Permission\PermissionRegistrar;

class UpdateAdminAction extends BaseAction
{
    public function __construct(
        protected AdminRepositoryInterface $repository,
        protected AdminGuardService $guard,
    ) {}

    /**
     * @param  array{admin: User, dto: UpdateAdminDTO}  $input
     */
    public function execute(mixed $input): User
    {
        /** @var User $admin */
        $admin = $input['admin'];

        /** @var UpdateAdminDTO $dto */
        $dto = $input['dto'];

        $updated = $this->executeWithTransaction(function () use ($admin, $dto): User {
            $this->guard->assertMayModify($admin);

            if ($this->repository->existsByEmail($dto->email, $admin->getKey())) {
                throw AdminOperationException::emailAlreadyExists();
            }

            $accountRole = $this->guard->resolveAccountRole(
                $dto->accountRole,
                $admin->role->value,
            );

            $roles = $dto->roles !== [] ? $dto->roles : [$accountRole];

            $this->guard->assertMayAssignRoles($roles);
            $this->guard->assertDeactivationIsSafe($admin, $dto->isActive);

            $admin = $this->repository->update($admin, $dto);

            // Account type is applied separately from the DTO's mass-assigned
            // attributes so it can never be set from unauthorised input.
            if ($admin->role->value !== $accountRole) {
                $admin->forceFill(['role' => $accountRole])->save();
            }

            return $this->repository->syncRoles($admin->refresh(), $roles);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $updated;
    }
}
