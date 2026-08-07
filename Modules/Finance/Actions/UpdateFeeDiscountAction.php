<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\UpdateFeeDiscountDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\FeeDiscount;
use Modules\Finance\Repositories\FeeDiscountRepositoryInterface;

class UpdateFeeDiscountAction extends BaseAction
{
    public function __construct(
        protected FeeDiscountRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{feeDiscount: FeeDiscount, dto: UpdateFeeDiscountDTO}  $input
     */
    public function execute(mixed $input): FeeDiscount
    {
        /** @var FeeDiscount $feeDiscount */
        $feeDiscount = $input['feeDiscount'];

        /** @var UpdateFeeDiscountDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($feeDiscount, $dto): FeeDiscount {
            if ($dto->gradeLevelId && $dto->classroomId) {
                throw FinanceOperationException::scopeMustBeGradeOrClassroom();
            }

            if ($dto->code && $this->repository->existsByCode($dto->code, $feeDiscount->id)) {
                throw FinanceOperationException::feeDiscountCodeAlreadyExists();
            }

            return $this->repository->update($feeDiscount, $dto)
                ->load(['gradeLevel', 'classroom']);
        });
    }
}
