<?php

namespace Modules\Timetable\DTOs;

final readonly class TimetableEntryDTO
{
    public function __construct(
        public int $dayOfWeek,
        public int $periodId,
        public ?int $subjectId,
        public ?int $teacherId,
        public ?int $classroomId,
        public bool $isBreak,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            dayOfWeek: (int) $row['day_of_week'],
            periodId: (int) $row['period_id'],
            subjectId: isset($row['subject_id']) ? (int) $row['subject_id'] : null,
            teacherId: isset($row['teacher_id']) ? (int) $row['teacher_id'] : null,
            classroomId: isset($row['classroom_id']) ? (int) $row['classroom_id'] : null,
            isBreak: (bool) ($row['is_break'] ?? false),
        );
    }
}
