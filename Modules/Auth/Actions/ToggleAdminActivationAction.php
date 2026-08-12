<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;
use Modules\Auth\Services\AdminGuardService;
use Modules\Core\Abstracts\BaseAction;

/**
 * Suspending an account without deleting it — the usual response to someone
 * leaving the school, since their id is still referenced by past records.
 */
class ToggleAdminActivationAction extends BaseAction
{
    public function __construct(
        protected AdminRepositoryInterface $repository,
        protected AdminGuardService $guard,
    ) {}

    /**
     * @param  array{admin: User, isActive: bool}  $input
     */
    public function execute(mixed $input): User
    {
        /** @var User $admin */
        $admin = $input['admin'];
        $isActive = (bool) $input['isActive'];

        return $this->executeWithTransaction(function () use ($admin, $isActive): User {
            $this->guard->assertMayModify($admin);
            $this->guard->assertDeactivationIsSafe($admin, $isActive);

            return $this->repository->setActive($admin, $isActive);
        });
    }
}
