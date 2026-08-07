<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\FeeDiscount;
use Modules\Finance\Repositories\FeeDiscountRepositoryInterface;

class RestoreFeeDiscountAction extends BaseAction
{
    public function __construct(
        protected FeeDiscountRepositoryInterface $repository,
    ) {}

    /**
     * @param  FeeDiscount  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var FeeDiscount $feeDiscount */
        $feeDiscount = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($feeDiscount));
    }
}
