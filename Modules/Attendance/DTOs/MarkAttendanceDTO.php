<?php

namespace Modules\Attendance\DTOs;

use Modules\Attendance\Http\Requests\StoreAttendanceRequest;

final readonly class MarkAttendanceDTO
{
    /**
     * @param  array<int, array{student_id: int, status: string, check_in_time: ?string, check_out_time: ?string}>  $students
     */
    public function __construct(
        public int $classId,
        public ?int $subjectId,
        public int $academicYearId,
        public ?int $semesterId,
        public string $date,
        public int $session,
        public array $students,
    ) {}

    public static function fromRequest(StoreAttendanceRequest $request): self
    {
        return new self(
            classId: (int) $request->validated('class_id'),
            subjectId: $request->validated('subject_id') ? (int) $request->validated('subject_id') : null,
            academicYearId: (int) $request->validated('academic_year_id'),
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            date: $request->validated('date'),
            session: (int) $request->validated('session'),
            students: $request->validated('students'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRecordArray(): array
    {
        return [
            'class_id' => $this->classId,
            'subject_id' => $this->subjectId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'date' => $this->date,
            'session' => $this->session,
        ];
    }
}
