<?php

namespace Modules\Meeting\DTOs;

use Modules\Academic\Services\AcademicYearService;
use Modules\Meeting\Http\Requests\UpdateMeetingRequest;

final readonly class UpdateMeetingDTO
{
    public function __construct(
        public string $title,
        public ?array $description,
        public ?string $password,
        public string $scheduledAt,
        public int $durationMinutes,
        public int $teacherId,
        public ?int $academicYearId,
        public ?int $semesterId,
    ) {}

    public static function fromRequest(UpdateMeetingRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            description: $request->validated('description'),
            password: $request->validated('password'),
            scheduledAt: $request->validated('scheduled_at'),
            durationMinutes: (int) $request->validated('duration_minutes'),
            teacherId: (int) $request->validated('teacher_id'),
            academicYearId: AcademicYearService::id(),
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
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
        ];
    }
}
