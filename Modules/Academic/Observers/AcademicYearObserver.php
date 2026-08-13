<?php

namespace Modules\Academic\Observers;

use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Services\AcademicContextService;

/**
 * Keeps "exactly one current academic year" true no matter which entry point
 * writes the row — Action, seeder, factory, or tinker. SetCurrentAcademicYearAction
 * still exists as the intent-revealing API; this is the backstop that makes the
 * invariant a property of the model rather than of one call path.
 */
class AcademicYearObserver
{
    public function saved(AcademicYear $academicYear): void
    {
        /*
         * Demote siblings only when this row was actually flipped to current,
         * otherwise every unrelated save would issue a pointless UPDATE.
         */
        if ($academicYear->wasChanged('is_current') || $academicYear->wasRecentlyCreated) {
            if ($academicYear->is_current) {
                $this->demoteOthers($academicYear);
            }
        }

        $this->flushCaches();
    }

    /**
     * A soft-deleted year must not keep the flag: restoring it later would
     * silently produce two current years, since the demotion in saved() only
     * runs for the row being promoted.
     */
    public function deleted(AcademicYear $academicYear): void
    {
        if ($academicYear->is_current) {
            AcademicYear::withTrashed()
                ->whereKey($academicYear->getKey())
                ->update(['is_current' => false]);

            /*
             * Keep the in-memory model in step with that query-builder write,
             * so a later restore() does not fire saved() with a stale
             * is_current and demote the year that took its place.
             */
            $academicYear->setAttribute('is_current', false)->syncOriginalAttribute('is_current');
        }

        $this->flushCaches();
    }

    public function restored(AcademicYear $academicYear): void
    {
        $this->flushCaches();
    }

    /**
     * Clears is_current on every other year without firing this observer
     * again — a plain query builder update bypasses model events, so there
     * is no recursion.
     */
    private function demoteOthers(AcademicYear $academicYear): void
    {
        AcademicYear::query()
            ->whereKeyNot($academicYear->getKey())
            ->where('is_current', true)
            ->update(['is_current' => false]);
    }

    /**
     * AcademicContextService::flush() cascades to AcademicYearService, so the
     * one call clears both memoised halves of the context.
     */
    private function flushCaches(): void
    {
        AcademicContextService::flush();
    }
}
