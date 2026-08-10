<?php

namespace Modules\SchoolClass\Actions;

use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\DTOs\UpdateClassDTO;
use Modules\SchoolClass\Exceptions\ClassOperationException;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Repositories\ClassRepositoryInterface;

class UpdateClassAction extends BaseAction
{
    public function __construct(
        protected ClassRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{class: SchoolClass, dto: UpdateClassDTO}  $input
     */
    public function execute(mixed $input): SchoolClass
    {
        /** @var SchoolClass $class */
        $class = $input['class'];
        /** @var UpdateClassDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($class, $dto): SchoolClass {
            if ($dto->capacity < 1) {
                throw ClassOperationException::invalidCapacity();
            }

            if ($this->repository->existsForGradeLevel($dto->academicYearId, $dto->gradeLevelId, $dto->name, $class->id)) {
                throw ClassOperationException::classAlreadyExists();
            }

            if ($dto->classroomId) {
                $classroom = Classroom::find($dto->classroomId);

                if (! $classroom) {
                    throw ClassOperationException::classroomNotFound();
                }
            }

            return $this->repository->update($class, $dto, auth()->id());
        });
    }
}
