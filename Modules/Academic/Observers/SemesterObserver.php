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

    /**
     * A soft-deleted semester must not keep the flag: restoring it later would
     * silently produce two current semesters, since the demotion in saved()
     * only runs for the row being promoted.
     */
    public function deleted(Semester $semester): void
    {
        if ($semester->is_current) {
            Semester::withTrashed()
                ->whereKey($semester->getKey())
                ->update(['is_current' => false]);

            /*
             * Keep the in-memory model in step with that query-builder write.
             * Leaving it stale makes a later restore() fire saved() with
             * is_current still true, which would demote whichever semester
             * became current in the meantime and leave none at all.
             */
            $semester->setAttribute('is_current', false)->syncOriginalAttribute('is_current');
        }

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
