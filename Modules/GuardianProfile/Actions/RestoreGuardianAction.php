<?php

namespace Modules\GuardianProfile\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;

/**
 * Reverses ArchiveGuardianAction: restores the soft-deleted profile and
 * reactivates the login.
 */
class RestoreGuardianAction extends BaseAction
{
    public function __construct(
        protected GuardianRepositoryInterface $repository,
    ) {}

    /**
     * @param  GuardianProfile  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var GuardianProfile $guardian */
        $guardian = $input;

        return $this->executeWithTransaction(function () use ($guardian): bool {
            $restored = $this->repository->restore($guardian);

            $guardian->user()->update(['is_active' => true]);

            return $restored;
        });
    }
}
