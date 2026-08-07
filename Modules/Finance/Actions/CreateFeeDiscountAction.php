<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateFeeDiscountDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\FeeDiscount;
use Modules\Finance\Repositories\FeeDiscountRepositoryInterface;

class CreateFeeDiscountAction extends BaseAction
{
    public function __construct(
        protected FeeDiscountRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateFeeDiscountDTO  $input
     */
    public function execute(mixed $input): FeeDiscount
    {
        /** @var CreateFeeDiscountDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): FeeDiscount {
            if ($dto->gradeLevelId && $dto->classroomId) {
                throw FinanceOperationException::scopeMustBeGradeOrClassroom();
            }

            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw FinanceOperationException::feeDiscountCodeAlreadyExists();
            }

            return $this->repository->create($dto, auth()->id())
                ->load(['gradeLevel', 'classroom']);
        });
    }
}
