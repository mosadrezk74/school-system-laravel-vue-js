<?php

namespace Modules\Academic\Observers;

use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Services\AcademicContextService;
use Modules\Academic\Services\AcademicYearService;

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

    public function deleted(AcademicYear $academicYear): void
    {
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

    private function flushCaches(): void
    {
        AcademicYearService::flush();
        AcademicContextService::flush();
    }
}
