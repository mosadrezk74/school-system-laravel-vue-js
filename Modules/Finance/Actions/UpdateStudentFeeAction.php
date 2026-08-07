<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\UpdateStudentFeeDTO;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Repositories\StudentFeeRepositoryInterface;

class UpdateStudentFeeAction extends BaseAction
{
    public function __construct(
        protected StudentFeeRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{studentFee: StudentFee, dto: UpdateStudentFeeDTO}  $input
     */
    public function execute(mixed $input): StudentFee
    {
        /** @var StudentFee $studentFee */
        $studentFee = $input['studentFee'];

        /** @var UpdateStudentFeeDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(
            fn (): StudentFee => $this->repository->update($studentFee, $dto)
                ->load(['student.user', 'feeStructure.feeCategory']),
        );
    }
}
