<?php

namespace Modules\StudyMaterial\Repositories;

use Illuminate\Http\UploadedFile;
use Modules\StudyMaterial\DTOs\CreateStudyResourceDTO;
use Modules\StudyMaterial\DTOs\UpdateStudyResourceDTO;
use Modules\StudyMaterial\Models\StudyResource;

class StudyResourceRepository implements StudyResourceRepositoryInterface
{
    public function findById(int $id): ?StudyResource
    {
        return StudyResource::query()->find($id);
    }

    public function findByIdOrFail(int $id): StudyResource
    {
        return StudyResource::query()->findOrFail($id);
    }

    public function create(CreateStudyResourceDTO $dto, ?int $actorId = null): StudyResource
    {
        return StudyResource::query()->create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(StudyResource $studyResource, UpdateStudyResourceDTO $dto, ?int $actorId = null): StudyResource
    {
        $studyResource->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $studyResource->refresh();
    }

    public function delete(StudyResource $studyResource): bool
    {
        return (bool) $studyResource->delete();
    }

    public function restore(StudyResource $studyResource): bool
    {
        return (bool) $studyResource->restore();
    }

    public function attachFile(StudyResource $studyResource, UploadedFile $file): StudyResource
    {
        $studyResource->addMedia($file)->toMediaCollection(StudyResource::MEDIA_COLLECTION);

        return $studyResource->refresh();
    }

    public function incrementDownloads(StudyResource $studyResource): StudyResource
    {
        $studyResource->increment('downloads_count');

        return $studyResource->refresh();
    }
}
