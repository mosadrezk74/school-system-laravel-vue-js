<?php

namespace Modules\StudyMaterial\Repositories;

use Illuminate\Http\UploadedFile;
use Modules\StudyMaterial\DTOs\CreateStudyResourceDTO;
use Modules\StudyMaterial\DTOs\UpdateStudyResourceDTO;
use Modules\StudyMaterial\Models\StudyResource;

interface StudyResourceRepositoryInterface
{
    public function findById(int $id): ?StudyResource;

    public function findByIdOrFail(int $id): StudyResource;

    public function create(CreateStudyResourceDTO $dto, ?int $actorId = null): StudyResource;

    public function update(StudyResource $studyResource, UpdateStudyResourceDTO $dto, ?int $actorId = null): StudyResource;

    public function delete(StudyResource $studyResource): bool;

    public function restore(StudyResource $studyResource): bool;

    public function attachFile(StudyResource $studyResource, UploadedFile $file): StudyResource;

    public function incrementDownloads(StudyResource $studyResource): StudyResource;
}
