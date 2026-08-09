<?php

namespace Modules\Academic\Interfaces\Repositories;

use Modules\Academic\DTOs\CreateGradeLevelDTO;
use Modules\Academic\DTOs\UpdateGradeLevelDTO;
use Modules\Academic\Models\GradeLevel;

interface GradeLevelRepositoryInterface
{
    public function findById(int $id): ?GradeLevel;

    public function findByIdOrFail(int $id): GradeLevel;

    public function withTrashed(int $id): ?GradeLevel;

    public function createFromDTO(CreateGradeLevelDTO $dto): GradeLevel;

    public function updateFromDTO(GradeLevel $gradeLevel, UpdateGradeLevelDTO $dto): GradeLevel;

    public function delete(GradeLevel $gradeLevel): bool;

    public function restore(GradeLevel $gradeLevel): bool;
}
