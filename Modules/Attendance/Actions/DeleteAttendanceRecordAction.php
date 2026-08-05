<?php

namespace Modules\Attendance\Actions;

use Modules\Attendance\Actions\Concerns\RecomputesAttendanceSummary;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class DeleteAttendanceRecordAction extends BaseAction
{
    use RecomputesAttendanceSummary;

    public function __construct(
        protected AttendanceRepositoryInterface $repository,
    ) {}

    /**
     * @param  AttendanceRecord  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var AttendanceRecord $record */
        $record = $input;

        return $this->executeWithTransaction(function () use ($record): bool {
            $studentIds = $this->repository->detailsForRecord($record->id)->pluck('student_id')->all();
            $academicYearId = $record->academic_year_id;
            $semesterId = $record->semester_id;
            $classId = $record->class_id;

            $deleted = $this->repository->deleteRecord($record);

            foreach ($studentIds as $studentId) {
                $this->recomputeSummary($studentId, $academicYearId, $semesterId, $classId);
            }

            return $deleted;
        });
    }
}
