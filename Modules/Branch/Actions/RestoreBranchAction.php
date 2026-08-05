<?php

namespace Modules\Branch\Actions;

use Modules\Branch\Interfaces\Repositories\BranchRepositoryInterface;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseAction;

class RestoreBranchAction extends BaseAction
{
    public function __construct(
        protected BranchRepositoryInterface $repository,
    ) {}

    /**
     * @param Branch $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Branch $branch */
        $branch = $input;

        return $this->executeWithTransaction(function () use ($branch): bool {
            return $this->repository->restore($branch->id);
        });
    }
}