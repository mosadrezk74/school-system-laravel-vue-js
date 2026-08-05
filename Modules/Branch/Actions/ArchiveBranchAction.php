<?php

namespace Modules\Branch\Actions;

use Modules\Branch\Exceptions\BranchOperationException;
use Modules\Branch\Interfaces\Repositories\BranchRepositoryInterface;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseAction;

class ArchiveBranchAction extends BaseAction
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
            if ($branch->is_main) {
                throw BranchOperationException::cannotDeleteMainBranch();
            }

            return $this->repository->delete($branch->id);
        });
    }
}