<?php

namespace Modules\Academic\Repositories;

use Modules\Academic\DTOs\CreateGradeLevelDTO;
use Modules\Academic\DTOs\UpdateGradeLevelDTO;
use Modules\Academic\Interfaces\Repositories\GradeLevelRepositoryInterface;
use Modules\Academic\Models\GradeLevel;

class GradeLevelRepository implements GradeLevelRepositoryInterface
{
    public function findById(int $id): ?GradeLevel
    {
        return GradeLevel::query()->find($id);
    }

    public function findByIdOrFail(int $id): GradeLevel
    {
        return GradeLevel::query()->findOrFail($id);
    }

    public function withTrashed(int $id): ?GradeLevel
    {
        return GradeLevel::withTrashed()->find($id);
    }

    public function createFromDTO(CreateGradeLevelDTO $dto): GradeLevel
    {
        return GradeLevel::query()->create($dto->toArray());
    }

    public function updateFromDTO(GradeLevel $gradeLevel, UpdateGradeLevelDTO $dto): GradeLevel
    {
        $gradeLevel->update($dto->toArray());

        return $gradeLevel->refresh();
    }

    public function delete(GradeLevel $gradeLevel): bool
    {
        return (bool) $gradeLevel->delete();
    }

    public function restore(GradeLevel $gradeLevel): bool
    {
        return (bool) $gradeLevel->restore();
    }
}
