<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class UpdateAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{assignment: Assignment, dto: UpdateAssignmentDTO}  $input
     */
    public function execute(mixed $input): Assignment
    {
        /** @var Assignment $assignment */
        $assignment = $input['assignment'];
        /** @var UpdateAssignmentDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($assignment, $dto): Assignment {
            return $this->repository->update($assignment, $dto, auth()->id());
        });
    }
}
