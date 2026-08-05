<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\DTOs\UpdateBehaviorRecordDTO;
use Modules\Behavior\Exceptions\BehaviorOperationException;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Behavior\Repositories\BehaviorRecordRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class UpdateBehaviorRecordAction extends BaseAction
{
    public function __construct(
        protected BehaviorRecordRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{record: BehaviorRecord, dto: UpdateBehaviorRecordDTO}  $input
     */
    public function execute(mixed $input): BehaviorRecord
    {
        /** @var BehaviorRecord $record */
        $record = $input['record'];
        /** @var UpdateBehaviorRecordDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($record, $dto): BehaviorRecord {
            $category = BehaviorCategory::find($dto->behaviorCategoryId);

            if (! $category) {
                throw BehaviorOperationException::categoryNotFound();
            }

            // Re-snapshot points whenever the category changed (or even if it
            // didn't — re-reading the current value here is harmless and
            // keeps this simple; the snapshot only ever needs to reflect
            // "points at last save", not "points at original creation").
            return $this->repository->update($record, $dto, $category->points);
        });
    }
}
