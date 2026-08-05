<?php

namespace Modules\Behavior\Repositories;

use Modules\Behavior\DTOs\CreateBehaviorRecordDTO;
use Modules\Behavior\DTOs\UpdateBehaviorRecordDTO;
use Modules\Behavior\Models\BehaviorRecord;

interface BehaviorRecordRepositoryInterface
{
    public function findById(int $id): ?BehaviorRecord;

    public function findByIdOrFail(int $id): BehaviorRecord;

    public function create(CreateBehaviorRecordDTO $dto, int $reportedBy, int $points): BehaviorRecord;

    public function update(BehaviorRecord $record, UpdateBehaviorRecordDTO $dto, int $points): BehaviorRecord;

    public function delete(BehaviorRecord $record): bool;
}
