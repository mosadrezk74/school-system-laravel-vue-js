<?php

namespace Modules\Academic\Actions;

use Modules\Academic\DTOs\CreateSemesterDTO;
use Modules\Academic\Exceptions\SemesterOperationException;
use Modules\Academic\Interfaces\Repositories\SemesterRepositoryInterface;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseAction;

class CreateSemesterAction extends BaseAction
{
    public function __construct(
        protected SemesterRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateSemesterDTO  $input
     */
    public function execute(mixed $input): Semester
    {
        /** @var CreateSemesterDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Semester {
            if ($this->repository->existsBySequence($dto->academicYearId, $dto->sequence)) {
                throw SemesterOperationException::sequenceAlreadyExists();
            }

            return $this->repository->createFromDTO($dto);
        });
    }
}
