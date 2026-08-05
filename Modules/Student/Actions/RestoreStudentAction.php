<?php

namespace Modules\Student\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Repositories\StudentRepositoryInterface;

/**
 * Reverses ArchiveStudentAction: restores the soft-deleted profile and
 * reactivates the login.
 */
class RestoreStudentAction extends BaseAction
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
            $restored = $this->repository->restore($student);

            $student->user()->update(['is_active' => true]);

            return $restored;
        });
    }
}
