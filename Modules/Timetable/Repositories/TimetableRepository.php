<?php

namespace Modules\Timetable\Repositories;

use Modules\Timetable\DTOs\CreateTimetableDTO;
use Modules\Timetable\Models\Timetable;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class TimetableRepository implements TimetableRepositoryInterface
{
    public function findById(int $id): ?Timetable
    {
        return Timetable::query()->find($id);
    }

    public function findByIdOrFail(int $id): Timetable
    {
        return Timetable::query()->findOrFail($id);
    }

    public function existsForClassAndYear(int $classId, int $academicYearId, ?int $excludeId = null): bool
    {
        return Timetable::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function findArchivedForClassAndYear(int $classId, int $academicYearId): ?Timetable
    {
        return Timetable::onlyTrashed()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    public function restoreWith(Timetable $timetable, CreateTimetableDTO $dto): Timetable
    {
        $timetable->restore();
        $timetable->update($dto->toArray());

        return $timetable->refresh();
    }

    public function create(CreateTimetableDTO $dto, ?int $actorId = null): Timetable
    {
        return Timetable::create($dto->toArray());
    }

    public function update(Timetable $timetable, CreateTimetableDTO $dto, ?int $actorId = null): Timetable
    {
        $timetable->update($dto->toArray());

        return $timetable->refresh();
    }

    public function delete(Timetable $timetable): bool
    {
        return (bool) $timetable->delete();
    }

    public function restore(Timetable $timetable): bool
    {
        return (bool) $timetable->restore();
    }
}
