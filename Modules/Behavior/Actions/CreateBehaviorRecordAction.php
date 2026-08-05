<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\DTOs\CreateBehaviorRecordDTO;
use Modules\Behavior\Exceptions\BehaviorOperationException;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Behavior\Repositories\BehaviorRecordRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class CreateBehaviorRecordAction extends BaseAction
{
    public function __construct(
        protected BehaviorRecordRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{dto: CreateBehaviorRecordDTO, reportedBy: int}  $input
     */
    public function execute(mixed $input): BehaviorRecord
    {
        /** @var CreateBehaviorRecordDTO $dto */
        $dto = $input['dto'];
        $reportedBy = $input['reportedBy'];

        return $this->executeWithTransaction(function () use ($dto, $reportedBy): BehaviorRecord {
            $category = BehaviorCategory::find($dto->behaviorCategoryId);

            if (! $category) {
                throw BehaviorOperationException::categoryNotFound();
            }

            if (! $category->is_active) {
                throw BehaviorOperationException::categoryInactive();
            }

            // points is never trusted from the client: it is always derived
            // from the category's current value at the moment of creation,
            // then stored as a snapshot so later edits to the category's
            // points don't rewrite history.
            return $this->repository->create($dto, $reportedBy, $category->points);
        });
    }
}
