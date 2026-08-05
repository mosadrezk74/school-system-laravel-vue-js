<?php

namespace Modules\Branch\Actions;

use Modules\Branch\DTOs\CreateBranchDTO;
use Modules\Branch\Exceptions\BranchOperationException;
use Modules\Branch\Interfaces\Repositories\BranchRepositoryInterface;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseAction;

class CreateBranchAction extends BaseAction
{
    public function __construct(
        protected BranchRepositoryInterface $repository,
    ) {}

    /**
     * @param CreateBranchDTO $input
     */
    public function execute(mixed $input): Branch
    {
        /** @var CreateBranchDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Branch {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw BranchOperationException::codeAlreadyExists();
            }

            if ($dto->isMain && $this->repository->getMainBranch()) {
                throw BranchOperationException::mainBranchAlreadyExists();
            }

            return $this->repository->createFromDTO($dto, auth()->id());
        });
    }
}