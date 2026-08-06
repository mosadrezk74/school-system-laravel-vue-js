<?php

namespace Modules\Attendance\DTOs;

use Modules\Attendance\Enums\AttendanceStatus;

final readonly class AttendanceReportRowDTO
{
    public function __construct(
        public int $studentId,
        public ?string $studentName,
        public ?string $admissionNumber,
        public int $presentCount,
        public int $absentCount,
        public int $lateCount,
        public int $excusedCount,
        public int $otherCount,
        public int $totalDays,
        public float $attendancePercentage,
        public float $absencePercentage,
    ) {}

    /**
     * @param  array<string, int>  $counts
     */
    public static function fromCounts(int $studentId, ?string $studentName, ?string $admissionNumber, array $counts): self
    {
        $present = $counts[AttendanceStatus::Present->value] ?? 0;
        $absent = $counts[AttendanceStatus::Absent->value] ?? 0;
        $late = $counts[AttendanceStatus::Late->value] ?? 0;
        $excused = $counts[AttendanceStatus::Excused->value] ?? 0;
        $other = $counts[AttendanceStatus::Other->value] ?? 0;

        $total = $present + $absent + $late + $excused + $other;

        return new self(
            studentId: $studentId,
            studentName: $studentName,
            admissionNumber: $admissionNumber,
            presentCount: $present,
            absentCount: $absent,
            lateCount: $late,
            excusedCount: $excused,
            otherCount: $other,
            totalDays: $total,
            attendancePercentage: $total > 0 ? round($present / $total * 100, 2) : 0.0,
            absencePercentage: $total > 0 ? round($absent / $total * 100, 2) : 0.0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'student_name' => $this->studentName,
            'admission_number' => $this->admissionNumber,
            'present_count' => $this->presentCount,
            'absent_count' => $this->absentCount,
            'late_count' => $this->lateCount,
            'excused_count' => $this->excusedCount,
            'other_count' => $this->otherCount,
            'total_days' => $this->totalDays,
            'attendance_percentage' => $this->attendancePercentage,
            'absence_percentage' => $this->absencePercentage,
        ];
    }
}
