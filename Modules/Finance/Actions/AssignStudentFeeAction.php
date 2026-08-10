<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateStudentFeeDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Repositories\StudentFeeRepositoryInterface;
use Modules\Finance\Services\StudentFeeCalculator;
use Modules\Student\Models\StudentProfile;

class AssignStudentFeeAction extends BaseAction
{
    public function __construct(
        protected StudentFeeRepositoryInterface $repository,
        protected StudentFeeCalculator $calculator,
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

            /*
             * The discount is recomputed here from the student's own
             * scholarship rather than taken from the request, so the amount a
             * form submits can never decide what a family is charged.
             */
            $student = StudentProfile::query()->findOrFail($dto->studentId);
            $amounts = $this->calculator->calculate($student, $dto->amount);

            return $this->repository->createWithAmounts($dto, $amounts)
                ->load(['student.user', 'feeStructure.feeCategory']);
        });
    }
}
