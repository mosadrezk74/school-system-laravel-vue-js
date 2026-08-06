<?php

namespace Modules\Meeting\Actions;

use Illuminate\Support\Carbon;
use Modules\Core\Abstracts\BaseAction;
use Modules\Meeting\DTOs\CreateMeetingDTO;
use Modules\Meeting\Exceptions\MeetingOperationException;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;
use Modules\Meeting\Services\JitsiRoomFactory;
use Modules\Timetable\Models\TimetableEntry;
use Modules\Timetable\Models\TimetableSession;
use Modules\Timetable\Repositories\TimetableSessionRepositoryInterface;

class StartMeetingForSessionAction extends BaseAction
{
    public function __construct(
        protected MeetingRepositoryInterface $meetingRepository,
        protected TimetableSessionRepositoryInterface $sessionRepository,
        protected JitsiRoomFactory $roomFactory,
    ) {}

    /**
     * Resolves (creating if needed) today's session for the given online
     * entry, then resolves (creating if needed) its Meeting and marks it
     * live. Calling this twice for the same entry/date reuses the same
     * Meeting rather than creating a duplicate.
     *
     * @param  array{entry: TimetableEntry, date: Carbon}  $input
     */
    public function execute(mixed $input): Meeting
    {
        $entry = $input['entry'];
        $date = $input['date'];

        return $this->executeWithTransaction(function () use ($entry, $date): Meeting {
            if (! $entry->is_online) {
                throw MeetingOperationException::invalidStatusTransition();
            }

            $session = $this->sessionRepository->firstOrCreateForDate($entry, $date);
            $session->loadMissing('meeting');

            $meeting = $session->meeting ?? $this->createMeetingForSession($entry, $session, $date);

            if ($meeting->status === 'scheduled') {
                $meeting = $this->meetingRepository->updateStatus($meeting, 'live', auth()->id());
            }

            return $meeting;
        });
    }

    private function createMeetingForSession(TimetableEntry $entry, TimetableSession $session, Carbon $date): Meeting
    {
        $entry->loadMissing(['timetable.class', 'timetable.academicYear', 'timetable.semester', 'period', 'subject']);

        $timetable = $entry->timetable;
        $startTime = $entry->period?->start_time ?? $entry->start_time;
        $endTime = $entry->period?->end_time ?? $entry->end_time;

        $scheduledAt = $date->copy()->setTimeFromTimeString($startTime);
        $durationMinutes = max(1, (int) $scheduledAt->diffInMinutes($date->copy()->setTimeFromTimeString($endTime)));

        $dto = new CreateMeetingDTO(
            title: $entry->subject?->name ?? __('meeting::meeting.fields.title'),
            description: null,
            password: null,
            scheduledAt: $scheduledAt->toDateTimeString(),
            durationMinutes: $durationMinutes,
            teacherId: $entry->teacher_id,
            academicYearId: $timetable?->academic_year_id,
            semesterId: $timetable?->semester_id,
            subjectId: $entry->subject_id,
            schoolClassId: $timetable?->class_id,
            timetableSessionId: $session->id,
        );

        $room = $this->roomFactory->make();

        return $this->meetingRepository->create($dto, $room['uuid'], $room['room_name'], $room['meeting_url'], auth()->id());
    }
}
