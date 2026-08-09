<?php

namespace Modules\SchoolClass\Repositories;

use Modules\SchoolClass\DTOs\CreateClassDTO;
use Modules\SchoolClass\DTOs\UpdateClassDTO;
use Modules\SchoolClass\Models\SchoolClass;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class ClassRepository implements ClassRepositoryInterface
{
    public function findById(int $id): ?SchoolClass
    {
        return SchoolClass::query()->find($id);
    }

    public function findByIdOrFail(int $id): SchoolClass
    {
        return SchoolClass::query()->findOrFail($id);
    }

    public function existsForGradeLevel(int $academicYearId, int $gradeLevelId, ?int $excludeId = null): bool
    {
        return SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->where('grade_level_id', $gradeLevelId)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateClassDTO $dto, ?int $actorId = null): SchoolClass
    {
        return SchoolClass::query()->create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(SchoolClass $class, UpdateClassDTO $dto, ?int $actorId = null): SchoolClass
    {
        $class->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $class->refresh();
    }

    public function delete(SchoolClass $class): bool
    {
        return (bool) $class->delete();
    }

    public function restore(SchoolClass $class): bool
    {
        return (bool) $class->restore();
    }
}
