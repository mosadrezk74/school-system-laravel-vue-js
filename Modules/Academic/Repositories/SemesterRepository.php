<?php

namespace Modules\Academic\Repositories;

use Modules\Academic\DTOs\CreateSemesterDTO;
use Modules\Academic\DTOs\UpdateSemesterDTO;
use Modules\Academic\Interfaces\Repositories\SemesterRepositoryInterface;
use Modules\Academic\Models\Semester;

class SemesterRepository implements SemesterRepositoryInterface
{
    public function findById(int $id): ?Semester
    {
        return Semester::query()->find($id);
    }

    public function findByIdOrFail(int $id): Semester
    {
        return Semester::query()->findOrFail($id);
    }

    public function withTrashed(int $id): ?Semester
    {
        return Semester::withTrashed()->find($id);
    }

    public function createFromDTO(CreateSemesterDTO $dto): Semester
    {
        return Semester::query()->create($dto->toArray());
    }

    public function updateFromDTO(Semester $semester, UpdateSemesterDTO $dto): Semester
    {
        $semester->update($dto->toArray());

        return $semester->refresh();
    }

    public function delete(Semester $semester): bool
    {
        return (bool) $semester->delete();
    }

    public function restore(Semester $semester): bool
    {
        return (bool) $semester->restore();
    }

    /**
     * Soft-deleted rows still occupy the unique index, so they are counted
     * here too — otherwise the insert passes validation and fails at the
     * database instead.
     */
    public function existsBySequence(int $academicYearId, int $sequence, ?int $excludeId = null): bool
    {
        return Semester::withTrashed()
            ->where('academic_year_id', $academicYearId)
            ->where('sequence', $sequence)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }
}
