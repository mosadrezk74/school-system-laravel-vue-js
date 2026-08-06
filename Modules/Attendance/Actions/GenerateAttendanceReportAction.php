<?php

namespace Modules\Attendance\Actions;

use Illuminate\Support\Collection;
use Modules\Attendance\DTOs\AttendanceReportFilterDTO;
use Modules\Attendance\DTOs\AttendanceReportRowDTO;
use Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\Student\Models\StudentProfile;

class GenerateAttendanceReportAction extends BaseAction
{
    public function __construct(
        protected AttendanceRepositoryInterface $repository,
    ) {}

    /**
     * @param  AttendanceReportFilterDTO  $input
     * @return array{rows: Collection<int, AttendanceReportRowDTO>, statistics: array<string, mixed>}
     */
    public function execute(mixed $input): array
    {
        /** @var AttendanceReportFilterDTO $filter */
        $filter = $input;

        $students = $this->repository->studentsForReport($filter);
        $counts = $this->repository->statusCountsByStudent($filter);

        $rows = $students->map(fn (StudentProfile $student): AttendanceReportRowDTO => AttendanceReportRowDTO::fromCounts(
            $student->id,
            $student->user?->name,
            $student->admission_number,
            $counts->get($student->id, []),
        ));

        return [
            'rows' => $rows,
            'statistics' => $this->statistics($rows),
        ];
    }

    /**
     * @param  Collection<int, AttendanceReportRowDTO>  $rows
     * @return array<string, mixed>
     */
    protected function statistics(Collection $rows): array
    {
        $totalStudents = $rows->count();
        $withRecords = $rows->where('totalDays', '>', 0);

        return [
            'total_students' => $totalStudents,
            'total_present' => (int) $rows->sum('presentCount'),
            'total_absent' => (int) $rows->sum('absentCount'),
            'total_late' => (int) $rows->sum('lateCount'),
            'total_excused' => (int) $rows->sum('excusedCount'),
            'total_other' => (int) $rows->sum('otherCount'),
            'average_attendance_percentage' => $withRecords->isNotEmpty()
                ? round($withRecords->avg('attendancePercentage'), 2)
                : 0.0,
            'average_absence_percentage' => $withRecords->isNotEmpty()
                ? round($withRecords->avg('absencePercentage'), 2)
                : 0.0,
        ];
    }
}
