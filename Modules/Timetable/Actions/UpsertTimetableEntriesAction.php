<?php

namespace Modules\Timetable\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Core\Abstracts\BaseAction;
use Modules\Timetable\DTOs\TimetableEntryDTO;
use Modules\Timetable\DTOs\UpsertTimetableEntriesDTO;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Exceptions\TimetableOperationException;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Repositories\TimetableEntryRepositoryInterface;
use Modules\Timetable\Repositories\TimetableRepositoryInterface;

/**
 * Replaces an entire week of entries for one timetable in a single atomic
 * write, so teacher/classroom conflicts are checked against the whole
 * proposed week at once rather than cell by cell.
 */
class UpsertTimetableEntriesAction extends BaseAction
{
    public function __construct(
        protected TimetableRepositoryInterface $timetableRepository,
        protected TimetableEntryRepositoryInterface $entryRepository,
    ) {}

    /**
     * @param  UpsertTimetableEntriesDTO  $input
     */
    public function execute(mixed $input): Timetable
    {
        /** @var UpsertTimetableEntriesDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Timetable {
            $timetable = $this->timetableRepository->findByIdOrFail($dto->timetableId);

            $periods = Period::query()
                ->whereIn('id', array_column($dto->entries, 'periodId'))
                ->get()
                ->keyBy('id');

            $conflicts = [];

            foreach ($dto->entries as $entry) {
                if ($entry->isBreak) {
                    continue;
                }

                if ($entry->teacherId && $this->entryRepository->teacherHasConflict($entry->teacherId, $entry->dayOfWeek, $entry->periodId, $timetable->id)) {
                    $conflicts[] = $this->teacherConflictMessage($entry, $periods->get($entry->periodId));
                }

                if ($entry->classroomId && $this->entryRepository->classroomHasConflict($entry->classroomId, $entry->dayOfWeek, $entry->periodId, $timetable->id)) {
                    $conflicts[] = $this->classroomConflictMessage($entry, $periods->get($entry->periodId));
                }
            }

            if ($conflicts !== []) {
                /*
                 * The timetable builder is an Inertia page reading form.errors,
                 * so conflicts have to travel back through the session. A
                 * TimetableOperationException renders as raw JSON on an HTML
                 * visit, which left the admin with an unreadable 422 and no
                 * indication of which cells clashed.
                 */
                if (! request()->expectsJson()) {
                    throw ValidationException::withMessages(['entries' => $conflicts]);
                }

                throw TimetableOperationException::conflicts($conflicts);
            }

            $rows = array_map(function (TimetableEntryDTO $entry) use ($timetable, $periods): array {
                $period = $periods->get($entry->periodId);

                return [
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $entry->dayOfWeek,
                    'period_id' => $entry->periodId,
                    'period_number' => $period?->period_number,
                    'start_time' => $period?->start_time,
                    'end_time' => $period?->end_time,
                    'subject_id' => $entry->subjectId,
                    'teacher_id' => $entry->teacherId,
                    'classroom_id' => $entry->classroomId,
                    'is_break' => $entry->isBreak,
                    'is_online' => $entry->isOnline,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $dto->entries);

            $this->entryRepository->replaceForTimetable($timetable->id, $rows);

            return $timetable->load(['entries.subject', 'entries.teacher', 'entries.classroom', 'entries.period']);
        });
    }

    private function teacherConflictMessage(TimetableEntryDTO $entry, ?Period $period): string
    {
        $teacherName = User::find($entry->teacherId)?->name ?? (string) $entry->teacherId;

        return __('timetable::timetable.conflicts.teacher', [
            'teacher' => $teacherName,
            'day' => __("timetable::timetable.days.{$this->weekdayName($entry->dayOfWeek)}"),
            'period' => $period?->period_number ?? $entry->periodId,
        ]);
    }

    private function classroomConflictMessage(TimetableEntryDTO $entry, ?Period $period): string
    {
        return __('timetable::timetable.conflicts.classroom', [
            'day' => __("timetable::timetable.days.{$this->weekdayName($entry->dayOfWeek)}"),
            'period' => $period?->period_number ?? $entry->periodId,
        ]);
    }

    private function weekdayName(int $dayOfWeek): string
    {
        return Weekday::from($dayOfWeek)->name;
    }
}
