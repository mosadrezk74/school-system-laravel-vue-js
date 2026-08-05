<?php

namespace Modules\Branch\Actions;

use Modules\Branch\DTOs\UpdateBranchDTO;
use Modules\Branch\Exceptions\BranchOperationException;
use Modules\Branch\Interfaces\Repositories\BranchRepositoryInterface;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseAction;

class UpdateBranchAction extends BaseAction
{
    public function __construct(
        protected BranchRepositoryInterface $repository,
    ) {}

    /**
     * @param array{ branch: Branch, dto: UpdateBranchDTO } $input
     */
    public function execute(mixed $input): Branch
    {
        /** @var Branch $branch */
        $branch = $input['branch'];
        /** @var UpdateBranchDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($branch, $dto): Branch {
            if ($dto->code && $this->repository->existsByCode($dto->code, $branch->id)) {
                throw BranchOperationException::codeAlreadyExists();
            }

            // If this branch is being set as main, ensure no other main exists
            if ($dto->isMain && !$branch->is_main) {
                $existingMain = $this->repository->getMainBranch();
                if ($existingMain && $existingMain->id !== $branch->id) {
                    throw BranchOperationException::mainBranchAlreadyExists();
                }
            }

            // ✅ Use the new updateFromDTO method
            return $this->repository->updateFromDTO($branch->id, $dto, auth()->id());
        });
    }
}