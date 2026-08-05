<?php

namespace Modules\Behavior\Repositories;

use Modules\Behavior\DTOs\CreateBehaviorRecordDTO;
use Modules\Behavior\DTOs\UpdateBehaviorRecordDTO;
use Modules\Behavior\Models\BehaviorRecord;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary, the points-snapshot rule,
 * and any auth() lookups — the actor id and the snapshot points value both
 * arrive here as explicit parameters.
 */
class BehaviorRecordRepository implements BehaviorRecordRepositoryInterface
{
    public function findById(int $id): ?BehaviorRecord
    {
        return BehaviorRecord::query()->find($id);
    }

    public function findByIdOrFail(int $id): BehaviorRecord
    {
        return BehaviorRecord::query()->findOrFail($id);
    }

    public function create(CreateBehaviorRecordDTO $dto, int $reportedBy, int $points): BehaviorRecord
    {
        return BehaviorRecord::create([
            'student_id' => $dto->studentId,
            'behavior_category_id' => $dto->behaviorCategoryId,
            'date' => $dto->date,
            'points' => $points,
            'description' => $dto->description,
            'reported_by' => $reportedBy,
            'status' => 'reported',
        ]);
    }

    public function update(BehaviorRecord $record, UpdateBehaviorRecordDTO $dto, int $points): BehaviorRecord
    {
        $record->update([
            'student_id' => $dto->studentId,
            'behavior_category_id' => $dto->behaviorCategoryId,
            'date' => $dto->date,
            'points' => $points,
            'description' => $dto->description,
            'status' => $dto->status,
            'reviewed_by' => $dto->reviewedBy,
        ]);

        return $record->refresh();
    }

    public function delete(BehaviorRecord $record): bool
    {
        return (bool) $record->delete();
    }
}
