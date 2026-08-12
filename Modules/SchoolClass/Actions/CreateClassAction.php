<?php

namespace Modules\SchoolClass\Actions;

use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\DTOs\CreateClassDTO;
use Modules\SchoolClass\Exceptions\ClassOperationException;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Repositories\ClassRepositoryInterface;

class CreateClassAction extends BaseAction
{
    public function __construct(
        protected ClassRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateClassDTO  $input
     */
    public function execute(mixed $input): SchoolClass
    {
        /** @var CreateClassDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): SchoolClass {
            if ($dto->capacity < 1) {
                throw ClassOperationException::invalidCapacity();
            }

            if ($this->repository->existsForGradeLevel($dto->gradeLevelId, $dto->name)) {
                throw ClassOperationException::classAlreadyExists();
            }

            if ($dto->classroomId) {
                $classroom = Classroom::find($dto->classroomId);

                if (! $classroom) {
                    throw ClassOperationException::classroomNotFound();
                }
            }

            return $this->repository->create($dto, auth()->id());
        });
    }
}
