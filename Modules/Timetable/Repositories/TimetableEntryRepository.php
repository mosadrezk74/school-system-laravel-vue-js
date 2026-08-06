<?php

namespace Modules\Timetable\Repositories;

use Modules\Timetable\Models\TimetableEntry;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class TimetableEntryRepository implements TimetableEntryRepositoryInterface
{
    public function replaceForTimetable(int $timetableId, array $rows): void
    {
        TimetableEntry::query()->where('timetable_id', $timetableId)->delete();

        if ($rows !== []) {
            TimetableEntry::query()->insert($rows);
        }
    }

    public function teacherHasConflict(int $teacherId, int $dayOfWeek, int $periodId, int $excludingTimetableId): bool
    {
        return TimetableEntry::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('period_id', $periodId)
            ->whereHas('timetable', function ($query) use ($excludingTimetableId) {
                $query->whereKeyNot($excludingTimetableId);
            })
            ->exists();
    }

    public function classroomHasConflict(?int $classroomId, int $dayOfWeek, int $periodId, int $excludingTimetableId): bool
    {
        if ($classroomId === null) {
            return false;
        }

        return TimetableEntry::query()
            ->where('classroom_id', $classroomId)
            ->where('day_of_week', $dayOfWeek)
            ->where('period_id', $periodId)
            ->whereHas('timetable', function ($query) use ($excludingTimetableId) {
                $query->whereKeyNot($excludingTimetableId);
            })
            ->exists();
    }
}
