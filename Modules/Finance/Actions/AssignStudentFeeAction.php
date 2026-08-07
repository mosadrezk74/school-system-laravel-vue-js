<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateStudentFeeDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Repositories\StudentFeeRepositoryInterface;

class AssignStudentFeeAction extends BaseAction
{
    public function __construct(
        protected StudentFeeRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateStudentFeeDTO  $input
     */
    public function execute(mixed $input): StudentFee
    {
        /** @var CreateStudentFeeDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): StudentFee {
            if ($this->repository->existsForStudent($dto->studentId, $dto->feeStructureId)) {
                throw FinanceOperationException::feeAlreadyAssigned();
            }

            return $this->repository->create($dto)
                ->load(['student.user', 'feeStructure.feeCategory']);
        });
    }
}
