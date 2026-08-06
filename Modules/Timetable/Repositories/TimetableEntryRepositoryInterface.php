<?php

namespace Modules\Timetable\Repositories;

interface TimetableEntryRepositoryInterface
{
    /**
     * Deletes every entry currently belonging to the timetable and inserts
     * the given rows in its place, inside the caller's transaction.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function replaceForTimetable(int $timetableId, array $rows): void;

    public function teacherHasConflict(int $teacherId, int $dayOfWeek, int $periodId, int $excludingTimetableId): bool;

    public function classroomHasConflict(?int $classroomId, int $dayOfWeek, int $periodId, int $excludingTimetableId): bool;
}
