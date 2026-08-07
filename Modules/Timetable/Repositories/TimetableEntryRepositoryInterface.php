<?php

namespace Modules\Timetable\Repositories;

use Illuminate\Support\Collection;
use Modules\Timetable\Models\TimetableEntry;

interface TimetableEntryRepositoryInterface
{
    /**
     * Deletes every entry currently belonging to the timetable and inserts
     * the given rows in its place, inside the caller's transaction.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function replaceForTimetable(int $timetableId, array $rows): void;

    /**
     * @return Collection<int, TimetableEntry>
     */
    public function forTeacher(int $teacherId, int $academicYearId, ?int $dayOfWeek = null): Collection;

    public function teacherHasConflict(int $teacherId, int $dayOfWeek, int $periodId, int $excludingTimetableId): bool;

    public function classroomHasConflict(?int $classroomId, int $dayOfWeek, int $periodId, int $excludingTimetableId): bool;
}
