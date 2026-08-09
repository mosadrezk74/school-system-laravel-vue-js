<?php

namespace Modules\Classroom\Actions;

use Modules\Classroom\Interfaces\Repositories\ClassroomRepositoryInterface;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;

class CreateClassroomAction extends BaseAction
{
    public function __construct(
        protected ClassroomRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): Classroom
    {
        $dto = $input;

        return $this->executeWithTransaction(
            fn (): Classroom => $this->repository->createFromDTO($dto, auth()->id()),
        );
    }
}
