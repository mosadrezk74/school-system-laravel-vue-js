<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateFeeStructureDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Repositories\FeeStructureRepositoryInterface;

class CreateFeeStructureAction extends BaseAction
{
    public function __construct(
        protected FeeStructureRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateFeeStructureDTO  $input
     */
    public function execute(mixed $input): FeeStructure
    {
        /** @var CreateFeeStructureDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): FeeStructure {
            if ($dto->gradeLevelId && $dto->classroomId) {
                throw FinanceOperationException::scopeMustBeGradeOrClassroom();
            }

            return $this->repository->create($dto, auth()->id())
                ->load(['feeCategory', 'gradeLevel', 'classroom']);
        });
    }
}
