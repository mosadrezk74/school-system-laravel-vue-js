<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class ArchiveAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentRepositoryInterface $repository,
    ) {}

    /**
     * @param  Assignment  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Assignment $assignment */
        $assignment = $input;

        return $this->executeWithTransaction(function () use ($assignment): bool {
            return $this->repository->delete($assignment);
        });
    }
}
