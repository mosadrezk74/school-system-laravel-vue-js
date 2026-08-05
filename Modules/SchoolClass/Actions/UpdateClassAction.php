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

            if ($this->repository->existsBySection($dto->academicYearId, $dto->gradeLevelId, $dto->sectionCode, $class->id)) {
                throw ClassOperationException::sectionAlreadyExists();
            }

            if ($dto->classroomId) {
                $classroom = Classroom::find($dto->classroomId);

                if (! $classroom || $classroom->branch_id !== $dto->branchId) {
                    throw ClassOperationException::classroomBranchMismatch();
                }
            }

            return $this->repository->update($class, $dto, auth()->id());
        });
    }
}
