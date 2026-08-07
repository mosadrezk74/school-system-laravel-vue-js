<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\UpdateFeeStructureDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Repositories\FeeStructureRepositoryInterface;

class UpdateFeeStructureAction extends BaseAction
{
    public function __construct(
        protected FeeStructureRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{feeStructure: FeeStructure, dto: UpdateFeeStructureDTO}  $input
     */
    public function execute(mixed $input): FeeStructure
    {
        /** @var FeeStructure $feeStructure */
        $feeStructure = $input['feeStructure'];

        /** @var UpdateFeeStructureDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($feeStructure, $dto): FeeStructure {
            if ($dto->gradeLevelId && $dto->classroomId) {
                throw FinanceOperationException::scopeMustBeGradeOrClassroom();
            }

            return $this->repository->update($feeStructure, $dto, auth()->id())
                ->load(['feeCategory', 'gradeLevel', 'classroom']);
        });
    }
}
