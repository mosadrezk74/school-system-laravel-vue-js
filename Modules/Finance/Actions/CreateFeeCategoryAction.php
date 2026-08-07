<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateFeeCategoryDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Repositories\FeeCategoryRepositoryInterface;

class CreateFeeCategoryAction extends BaseAction
{
    public function __construct(
        protected FeeCategoryRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateFeeCategoryDTO  $input
     */
    public function execute(mixed $input): FeeCategory
    {
        /** @var CreateFeeCategoryDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): FeeCategory {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw FinanceOperationException::feeCategoryCodeAlreadyExists();
            }

            return $this->repository->create($dto);
        });
    }
}
