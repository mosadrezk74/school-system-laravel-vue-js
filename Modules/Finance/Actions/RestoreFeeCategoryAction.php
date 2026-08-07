<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Repositories\FeeCategoryRepositoryInterface;

class RestoreFeeCategoryAction extends BaseAction
{
    public function __construct(
        protected FeeCategoryRepositoryInterface $repository,
    ) {}

    /**
     * @param  FeeCategory  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var FeeCategory $feeCategory */
        $feeCategory = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($feeCategory));
    }
}
