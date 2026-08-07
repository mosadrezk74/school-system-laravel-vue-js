<?php

namespace Modules\GuardianProfile\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\GuardianProfile\DTOs\LinkGuardianStudentDTO;
use Modules\GuardianProfile\Exceptions\GuardianOperationException;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;

class LinkStudentToGuardianAction extends BaseAction
{
    public function __construct(
        protected GuardianRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{guardian: GuardianProfile, dto: LinkGuardianStudentDTO}  $input
     */
    public function execute(mixed $input): GuardianStudent
    {
        /** @var GuardianProfile $guardian */
        $guardian = $input['guardian'];

        /** @var LinkGuardianStudentDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($guardian, $dto): GuardianStudent {
            /*
             * The unique index on parent_student is the last-resort net. The
             * duplicate is rejected here first so the caller receives a
             * translated business error rather than an SQL constraint failure.
             */
            if ($this->repository->isLinked($guardian, $dto->studentId)) {
                throw GuardianOperationException::studentAlreadyLinked();
            }

            return $this->repository->linkStudent($guardian, $dto);
        });
    }
}
