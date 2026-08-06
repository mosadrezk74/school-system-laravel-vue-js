<?php

namespace Modules\Timetable\Repositories;

use Modules\Timetable\DTOs\CreateTimetableDTO;
use Modules\Timetable\Models\Timetable;

interface TimetableRepositoryInterface
{
    public function findById(int $id): ?Timetable;

    public function findByIdOrFail(int $id): Timetable;

    public function existsForClassAndYear(int $classId, int $academicYearId, ?int $excludeId = null): bool;

    public function create(CreateTimetableDTO $dto, ?int $actorId = null): Timetable;

    public function update(Timetable $timetable, CreateTimetableDTO $dto, ?int $actorId = null): Timetable;

    public function delete(Timetable $timetable): bool;

    public function restore(Timetable $timetable): bool;
}
