<?php

namespace Modules\Student\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Repositories\StudentRepositoryInterface;

/**
 * Archives a student: soft-deletes the profile and deactivates the login,
 * without ever hard-deleting either. Pair with RestoreStudentAction.
 */
class ArchiveStudentAction extends BaseAction
{
    public function __construct(
        protected StudentRepositoryInterface $repository,
    ) {}

    /**
     * @param  StudentProfile  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var StudentProfile $student */
        $student = $input;

        return $this->executeWithTransaction(function () use ($student): bool {
            $archived = $this->repository->delete($student);

            // The user account is only deactivated, not soft-deleted: other
            // tables keep referencing user_id for audit trails.
            $student->user()->update(['is_active' => false]);

            return $archived;
        });
    }
}
