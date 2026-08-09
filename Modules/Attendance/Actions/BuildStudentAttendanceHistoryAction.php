<?php

namespace Modules\Attendance\Actions;

use Modules\Attendance\DTOs\AttendanceReportRowDTO;
use Modules\Attendance\DTOs\StudentAttendanceFilterDTO;
use Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\Student\Models\StudentProfile;

class BuildStudentAttendanceHistoryAction extends BaseAction
{
    public function __construct(
        protected AttendanceRepositoryInterface $repository,
    ) {}

    /**
     * @param  StudentAttendanceFilterDTO  $input
     * @return array<string, mixed>
     */
    public function execute(mixed $input): array
    {
        /** @var StudentAttendanceFilterDTO $filter */
        $filter = $input;

        $student = StudentProfile::query()
            ->with(['user:id,name,email', 'currentClass:id,name', 'academicYear:id,name'])
            ->findOrFail($filter->studentId);

        $counts = $this->repository->statusCountsForStudent($filter);

        $summary = AttendanceReportRowDTO::fromCounts(
            $student->id,
            $student->user?->name,
            $student->admission_number,
            $counts,
        );

        return [
            'student' => $student,
            'summary' => $summary,
            'timeline' => $this->repository->timelineForStudent($filter),
        ];
    }
}
