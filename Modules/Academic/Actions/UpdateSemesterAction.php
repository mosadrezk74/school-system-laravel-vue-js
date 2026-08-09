<?php

namespace Modules\Academic\Actions;

use Modules\Academic\DTOs\UpdateSemesterDTO;
use Modules\Academic\Exceptions\SemesterOperationException;
use Modules\Academic\Interfaces\Repositories\SemesterRepositoryInterface;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseAction;

class UpdateSemesterAction extends BaseAction
{
    public function __construct(
        protected SemesterRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{semester: Semester, dto: UpdateSemesterDTO}  $input
     */
    public function execute(mixed $input): Semester
    {
        /** @var Semester $semester */
        $semester = $input['semester'];
        /** @var UpdateSemesterDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($semester, $dto): Semester {
            if ($this->repository->existsBySequence($dto->academicYearId, $dto->sequence, $semester->id)) {
                throw SemesterOperationException::sequenceAlreadyExists();
            }

            return $this->repository->updateFromDTO($semester, $dto);
        });
    }
}
