<?php

namespace Modules\Meeting\DTOs;

use Modules\Meeting\Http\Requests\StoreMeetingRequest;

final readonly class CreateMeetingDTO
{
    public function __construct(
        public string $title,
        public ?array $description,
        public ?string $password,
        public string $scheduledAt,
        public int $durationMinutes,
        public int $teacherId,
        public ?int $branchId,
        public ?int $academicYearId,
        public ?int $semesterId,
        public ?int $subjectId = null,
        public ?int $schoolClassId = null,
        public ?int $timetableSessionId = null,
    ) {}

    public static function fromRequest(StoreMeetingRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            description: $request->validated('description'),
            password: $request->validated('password'),
            scheduledAt: $request->validated('scheduled_at'),
            durationMinutes: (int) $request->validated('duration_minutes'),
            teacherId: (int) $request->validated('teacher_id'),
            branchId: $request->validated('branch_id') ? (int) $request->validated('branch_id') : null,
            academicYearId: $request->validated('academic_year_id') ? (int) $request->validated('academic_year_id') : null,
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'password' => $this->password,
            'scheduled_at' => $this->scheduledAt,
            'duration_minutes' => $this->durationMinutes,
            'teacher_id' => $this->teacherId,
            'branch_id' => $this->branchId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'subject_id' => $this->subjectId,
            'school_class_id' => $this->schoolClassId,
            'timetable_session_id' => $this->timetableSessionId,
        ];
    }
}
