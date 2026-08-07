<?php

namespace Modules\GuardianProfile\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;

class UnlinkStudentFromGuardianAction extends BaseAction
{
    public function __construct(
        protected GuardianRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{guardian: GuardianProfile, studentId: int}  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var GuardianProfile $guardian */
        $guardian = $input['guardian'];

        $studentId = (int) $input['studentId'];

        return $this->executeWithTransaction(
            fn (): bool => $this->repository->unlinkStudent($guardian, $studentId),
        );
    }
}
