<?php

namespace Modules\Classroom\Actions;

use Modules\Classroom\Interfaces\Repositories\ClassroomRepositoryInterface;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;

class ArchiveClassroomAction extends BaseAction
{
    public function __construct(
        protected ClassroomRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): bool
    {
        $classroom = $input;

        return $this->executeWithTransaction(function () use ($classroom): bool {
            return $this->repository->delete($classroom->id);
        });
    }
}
