<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Modules\Auth\DTOs\UpdateProfileDTO;
use Modules\Auth\Repositories\ProfileRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class UpdateProfileAction extends BaseAction
{
    public function __construct(
        protected ProfileRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{user: User, dto: UpdateProfileDTO}  $input
     */
    public function execute(mixed $input): User
    {
        /** @var User $user */
        $user = $input['user'];
        /** @var UpdateProfileDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(fn (): User => $this->repository->updateProfile($user, $dto));
    }
}
