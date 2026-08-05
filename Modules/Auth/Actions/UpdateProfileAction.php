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

        return $this->executeWithTransaction(function () use ($user, $dto): User {
            $emailChanged = $dto->email !== $user->email;

            $user = $this->repository->updateProfile($user, $dto);

            // A changed email is unverified until proven again: the old
            // verification only attested ownership of the previous address.
            if ($emailChanged) {
                $user->email_verified_at = null;
                $user->save();
            }

            return $user;
        });
    }
}
