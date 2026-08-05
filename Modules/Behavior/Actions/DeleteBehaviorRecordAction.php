<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\Models\BehaviorRecord;
use Modules\Behavior\Repositories\BehaviorRecordRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

/**
 * behavior_records has no deleted_at column, so this is a hard delete —
 * there is no Restore counterpart, unlike BehaviorCategory.
 */
class DeleteBehaviorRecordAction extends BaseAction
{
    public function __construct(
        protected BehaviorRecordRepositoryInterface $repository,
    ) {}

    /**
     * @param  BehaviorRecord  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var BehaviorRecord $record */
        $record = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->delete($record));
    }
}
