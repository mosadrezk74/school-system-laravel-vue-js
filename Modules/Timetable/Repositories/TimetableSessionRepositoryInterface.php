<?php

namespace Modules\Timetable\Repositories;

use Illuminate\Support\Carbon;
use Modules\Timetable\Models\TimetableEntry;
use Modules\Timetable\Models\TimetableSession;

interface TimetableSessionRepositoryInterface
{
    public function findById(int $id): ?TimetableSession;

    public function findByIdOrFail(int $id): TimetableSession;

    public function firstOrCreateForDate(TimetableEntry $entry, Carbon $date): TimetableSession;
}
