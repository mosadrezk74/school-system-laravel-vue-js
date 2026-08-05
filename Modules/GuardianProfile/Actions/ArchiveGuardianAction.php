<?php

namespace Modules\GuardianProfile\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;

/**
 * Archives a guardian: soft-deletes the profile and deactivates the login,
 * without ever hard-deleting either. Pair with RestoreGuardianAction.
 *
 * Existing parent_student links are left untouched: they are the historical
 * record of who was linked to which student, and archiving a guardian's
 * account is not the same operation as unlinking them from a child.
 */
class ArchiveGuardianAction extends BaseAction
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
            $archived = $this->repository->delete($guardian);

            // The user account is only deactivated, not soft-deleted: other
            // tables keep referencing user_id for audit trails.
            $guardian->user()->update(['is_active' => false]);

            return $archived;
        });
    }
}
