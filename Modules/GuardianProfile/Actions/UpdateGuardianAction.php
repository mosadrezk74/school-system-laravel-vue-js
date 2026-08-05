<?php

namespace Modules\GuardianProfile\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\GuardianProfile\DTOs\UpdateGuardianDTO;
use Modules\GuardianProfile\Exceptions\GuardianOperationException;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;

class UpdateGuardianAction extends BaseAction
{
    public function __construct(
        protected GuardianRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{guardian: GuardianProfile, dto: UpdateGuardianDTO}  $input
     */
    public function execute(mixed $input): GuardianProfile
    {
        /** @var GuardianProfile $guardian */
        $guardian = $input['guardian'];
        /** @var UpdateGuardianDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($guardian, $dto): GuardianProfile {
            if ($this->repository->existsByEmail($dto->email, $guardian->user_id)) {
                throw GuardianOperationException::emailAlreadyExists();
            }

            if ($this->repository->existsByUsername($dto->username, $guardian->user_id)) {
                throw GuardianOperationException::usernameAlreadyExists();
            }

            $guardian = $this->repository->update($guardian, $dto, auth()->id());

            return $guardian->load('user');
        });
    }
}
