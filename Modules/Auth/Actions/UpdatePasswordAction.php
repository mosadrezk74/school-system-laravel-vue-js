<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Modules\Auth\DTOs\UpdatePasswordDTO;
use Modules\Auth\Repositories\ProfileRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class UpdatePasswordAction extends BaseAction
{
    public function __construct(
        protected ProfileRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{user: User, dto: UpdatePasswordDTO}  $input
     */
    public function execute(mixed $input): User
    {
        /** @var User $user */
        $user = $input['user'];
        /** @var UpdatePasswordDTO $dto */
        $dto = $input['dto'];

        $this->repository->updatePassword($user, $dto->password);

        return $user;
    }
}
