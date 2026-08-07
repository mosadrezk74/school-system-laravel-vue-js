<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Repositories\FeeStructureRepositoryInterface;

class ArchiveFeeStructureAction extends BaseAction
{
    public function __construct(
        protected FeeStructureRepositoryInterface $repository,
    ) {}

    /**
     * @param  FeeStructure  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var FeeStructure $feeStructure */
        $feeStructure = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->delete($feeStructure));
    }
}
