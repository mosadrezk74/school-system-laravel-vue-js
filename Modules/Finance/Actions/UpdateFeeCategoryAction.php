<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\UpdateFeeCategoryDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Repositories\FeeCategoryRepositoryInterface;

class UpdateFeeCategoryAction extends BaseAction
{
    public function __construct(
        protected FeeCategoryRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{feeCategory: FeeCategory, dto: UpdateFeeCategoryDTO}  $input
     */
    public function execute(mixed $input): FeeCategory
    {
        /** @var FeeCategory $feeCategory */
        $feeCategory = $input['feeCategory'];

        /** @var UpdateFeeCategoryDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($feeCategory, $dto): FeeCategory {
            if ($dto->code && $this->repository->existsByCode($dto->code, $feeCategory->id)) {
                throw FinanceOperationException::feeCategoryCodeAlreadyExists();
            }

            return $this->repository->update($feeCategory, $dto);
        });
    }
}
