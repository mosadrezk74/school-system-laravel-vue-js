<?php

namespace Modules\Assignment\Repositories;

use Modules\Assignment\DTOs\CreateStudyMaterialDTO;
use Modules\Assignment\DTOs\UpdateStudyMaterialDTO;
use Modules\Assignment\Models\StudyMaterial;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class StudyMaterialRepository implements StudyMaterialRepositoryInterface
{
    public function findById(int $id): ?StudyMaterial
    {
        return StudyMaterial::query()->find($id);
    }

    public function findByIdOrFail(int $id): StudyMaterial
    {
        return StudyMaterial::query()->findOrFail($id);
    }

    public function create(CreateStudyMaterialDTO $dto, ?int $actorId = null): StudyMaterial
    {
        return StudyMaterial::create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(StudyMaterial $studyMaterial, UpdateStudyMaterialDTO $dto, ?int $actorId = null): StudyMaterial
    {
        $studyMaterial->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $studyMaterial->refresh();
    }

    public function delete(StudyMaterial $studyMaterial): bool
    {
        return (bool) $studyMaterial->delete();
    }

    public function restore(StudyMaterial $studyMaterial): bool
    {
        return (bool) $studyMaterial->restore();
    }
}
