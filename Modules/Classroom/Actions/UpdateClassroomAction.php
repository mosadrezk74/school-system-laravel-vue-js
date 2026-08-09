<?php

namespace Modules\Classroom\Actions;

use Modules\Classroom\Interfaces\Repositories\ClassroomRepositoryInterface;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;

class UpdateClassroomAction extends BaseAction
{
    public function __construct(
        protected ClassroomRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): Classroom
    {
        $classroom = $input['classroom'];
        $dto = $input['dto'];

        return $this->executeWithTransaction(
            fn (): Classroom => $this->repository->updateFromDTO($classroom->id, $dto, auth()->id()),
        );
    }
}
