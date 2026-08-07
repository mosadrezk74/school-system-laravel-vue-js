<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Services\PortalScopeService;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Models\TimetableEntry;

class StudentScheduleController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $classIds = $this->scope->accessibleClassIds() ?? [];
        $day = $request->filled('day') ? (int) $request->input('day') : null;

        return $this->render('Portal::Student/Schedule', [
            'entries' => $this->entriesFor($classIds, $day),
            'filters' => ['day' => $day],
            'weekdays' => Weekday::options(),
            'today' => (int) now()->dayOfWeek,
        ]);
    }

    /**
     * @param  array<int, int>  $classIds
     * @return array<int, array<string, mixed>>
     */
    private function entriesFor(array $classIds, ?int $day): array
    {
        if ($classIds === []) {
            return [];
        }

        return TimetableEntry::query()
            ->with(['subject', 'classroom', 'teacher', 'timetable.class'])
            ->when($day !== null, fn ($query) => $query->where('day_of_week', $day))
            ->whereHas('timetable', fn ($query) => $query->whereIn('class_id', $classIds)->where('is_active', true))
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get()
            ->map(fn (TimetableEntry $entry): array => [
                'id' => $entry->id,
                'day_of_week' => $entry->day_of_week,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
                'is_break' => (bool) $entry->is_break,
                'subject' => $entry->subject?->name,
                'teacher' => $entry->teacher?->name,
                'classroom' => $entry->classroom?->name,
            ])
            ->all();
    }
}
