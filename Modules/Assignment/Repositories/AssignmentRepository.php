<?php

namespace Modules\Assignment\Repositories;

use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Models\Assignment;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class AssignmentRepository implements AssignmentRepositoryInterface
{
    public function findById(int $id): ?Assignment
    {
        return Assignment::query()->find($id);
    }

    public function findByIdOrFail(int $id): Assignment
    {
        return Assignment::query()->findOrFail($id);
    }

    public function create(CreateAssignmentDTO $dto, ?int $actorId = null): Assignment
    {
        return Assignment::create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(Assignment $assignment, UpdateAssignmentDTO $dto, ?int $actorId = null): Assignment
    {
        $assignment->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $assignment->refresh();
    }

    public function delete(Assignment $assignment): bool
    {
        return (bool) $assignment->delete();
    }

    public function restore(Assignment $assignment): bool
    {
        return (bool) $assignment->restore();
    }
}
