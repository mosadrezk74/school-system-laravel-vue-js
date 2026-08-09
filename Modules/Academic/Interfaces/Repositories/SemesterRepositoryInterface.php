<?php

namespace Modules\Academic\Interfaces\Repositories;

use Modules\Academic\DTOs\CreateSemesterDTO;
use Modules\Academic\DTOs\UpdateSemesterDTO;
use Modules\Academic\Models\Semester;

interface SemesterRepositoryInterface
{
    public function findById(int $id): ?Semester;

    public function findByIdOrFail(int $id): Semester;

    public function withTrashed(int $id): ?Semester;

    public function createFromDTO(CreateSemesterDTO $dto): Semester;

    public function updateFromDTO(Semester $semester, UpdateSemesterDTO $dto): Semester;

    public function delete(Semester $semester): bool;

    public function restore(Semester $semester): bool;

    /** Guards the (academic_year_id, sequence) unique constraint. */
    public function existsBySequence(int $academicYearId, int $sequence, ?int $excludeId = null): bool;
}
