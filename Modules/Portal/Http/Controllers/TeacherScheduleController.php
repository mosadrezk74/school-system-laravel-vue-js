<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Timetable\Actions\GetTeacherScheduleAction;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Models\TimetableEntry;

class TeacherScheduleController extends BaseInertiaController
{
    public function index(Request $request, GetTeacherScheduleAction $action): Response
    {
        $day = $request->filled('day') ? (int) $request->input('day') : null;

        return $this->render('Portal::Teacher/Schedule', [
            'entries' => $action->execute(['dayOfWeek' => $day])
                ->map(fn (TimetableEntry $entry): array => [
                    'id' => $entry->id,
                    'day_of_week' => $entry->day_of_week,
                    'period_number' => $entry->period_number,
                    'start_time' => $entry->start_time,
                    'end_time' => $entry->end_time,
                    'is_break' => (bool) $entry->is_break,
                    'is_online' => (bool) $entry->is_online,
                    'subject' => $entry->subject?->name,
                    'classroom' => $entry->classroom?->name,
                    'class_name' => $entry->timetable?->class?->name,
                ])
                ->values(),
            'filters' => ['day' => $day],
            'weekdays' => Weekday::options(),
            'today' => (int) now()->dayOfWeek,
        ]);
    }
}
