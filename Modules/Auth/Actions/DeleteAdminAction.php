<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;
use Modules\Auth\Services\AdminGuardService;
use Modules\Core\Abstracts\BaseAction;

class DeleteAdminAction extends BaseAction
{
    public function __construct(
        protected AdminRepositoryInterface $repository,
        protected AdminGuardService $guard,
    ) {}

    /**
     * @param  User  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var User $admin */
        $admin = $input;

        return $this->executeWithTransaction(function () use ($admin): bool {
            $this->guard->assertNotSelfDelete($admin);
            $this->guard->assertMayModify($admin);
            $this->guard->assertAdministratorsRemain($admin);

            return $this->repository->delete($admin);
        });
    }
}
