<?php

namespace Modules\Timetable\Repositories;

use Illuminate\Support\Carbon;
use Modules\Timetable\Models\TimetableEntry;
use Modules\Timetable\Models\TimetableSession;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class TimetableSessionRepository implements TimetableSessionRepositoryInterface
{
    public function findById(int $id): ?TimetableSession
    {
        return TimetableSession::query()->find($id);
    }

    public function findByIdOrFail(int $id): TimetableSession
    {
        return TimetableSession::query()->findOrFail($id);
    }

    public function firstOrCreateForDate(TimetableEntry $entry, Carbon $date): TimetableSession
    {
        return TimetableSession::query()->firstOrCreate([
            'timetable_entry_id' => $entry->id,
            'session_date' => $date->toDateString(),
        ], [
            'status' => 'scheduled',
        ]);
    }
}
