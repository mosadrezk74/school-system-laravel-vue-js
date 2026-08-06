<?php

namespace Modules\Attendance\Services;

use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Modules\Student\Models\StudentProfile;

class AttendanceRosterService
{
    public function __construct(
        private readonly AttendanceRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forClassAndDate(int $classId, string $date, int $session = 1): array
    {
        $record = $this->repository->findRecordByClassDateSession($classId, $date, $session);

        $existing = $record
            ? $this->repository->detailsForRecord($record->id)->keyBy('student_id')
            : collect();

        return StudentProfile::query()
            ->where('current_class_id', $classId)
            ->where('is_active', true)
            ->with('user:id,name')
            ->get(['id', 'user_id', 'admission_number'])
            ->map(function (StudentProfile $student) use ($existing): array {
                $detail = $existing->get($student->id);

                return [
                    'student_id' => $student->id,
                    'name' => $student->user?->name,
                    'admission_number' => $student->admission_number,
                    'status' => $detail?->status?->value ?? AttendanceStatus::Present->value,
                    'notes' => $detail?->notes,
                ];
            })
            ->values()
            ->all();
    }
}
