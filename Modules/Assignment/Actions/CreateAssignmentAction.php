<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class CreateAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateAssignmentDTO  $input
     */
    public function execute(mixed $input): Assignment
    {
        /** @var CreateAssignmentDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Assignment {
            return $this->repository->create($dto, auth()->id());
        });
    }
}
