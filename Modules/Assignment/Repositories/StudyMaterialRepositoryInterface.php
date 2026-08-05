<?php

namespace Modules\Assignment\Repositories;

use Modules\Assignment\DTOs\CreateStudyMaterialDTO;
use Modules\Assignment\DTOs\UpdateStudyMaterialDTO;
use Modules\Assignment\Models\StudyMaterial;

interface StudyMaterialRepositoryInterface
{
    public function findById(int $id): ?StudyMaterial;

    public function findByIdOrFail(int $id): StudyMaterial;

    public function create(CreateStudyMaterialDTO $dto, ?int $actorId = null): StudyMaterial;

    public function update(StudyMaterial $studyMaterial, UpdateStudyMaterialDTO $dto, ?int $actorId = null): StudyMaterial;

    public function delete(StudyMaterial $studyMaterial): bool;

    public function restore(StudyMaterial $studyMaterial): bool;
}
