<?php

namespace Modules\Academic\Observers;

use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;

/**
 * Mirrors AcademicYearObserver for semesters, with one difference: a semester
 * belongs to a year, so "current" is demoted globally rather than per year.
 * The app resolves a single current semester at a time, and letting an old
 * year keep a current semester would make that resolution ambiguous whenever
 * the current year changes.
 */
class SemesterObserver
{
    public function saved(Semester $semester): void
    {
        if ($semester->wasChanged('is_current') || $semester->wasRecentlyCreated) {
            if ($semester->is_current) {
                $this->demoteOthers($semester);
            }
        }

        AcademicContextService::flush();
    }

    public function deleted(Semester $semester): void
    {
        AcademicContextService::flush();
    }

    public function restored(Semester $semester): void
    {
        AcademicContextService::flush();
    }

    /**
     * Uses the query builder so no model events fire — this observer cannot
     * re-enter itself.
     */
    private function demoteOthers(Semester $semester): void
    {
        Semester::query()
            ->whereKeyNot($semester->getKey())
            ->where('is_current', true)
            ->update(['is_current' => false]);
    }
}
