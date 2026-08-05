<?php

namespace Modules\GuardianProfile\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\GuardianProfile\DTOs\CreateGuardianDTO;
use Modules\GuardianProfile\Exceptions\GuardianOperationException;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;

class CreateGuardianAction extends BaseAction
{
    public function __construct(
        protected GuardianRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateGuardianDTO  $input
     */
    public function execute(mixed $input): GuardianProfile
    {
        /** @var CreateGuardianDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): GuardianProfile {
            if ($this->repository->existsByEmail($dto->email)) {
                throw GuardianOperationException::emailAlreadyExists();
            }

            if ($this->repository->existsByUsername($dto->username)) {
                throw GuardianOperationException::usernameAlreadyExists();
            }

            // User::password is cast 'hashed', so the plain value from the
            // DTO is hashed automatically on save — no manual bcrypt here.
            $guardian = $this->repository->create($dto, auth()->id());

            $guardian->user->assignRole('guardian');

            return $guardian->load('user');
        });
    }
}
