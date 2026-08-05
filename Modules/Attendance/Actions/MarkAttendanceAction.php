<?php

namespace Modules\Attendance\Actions;

use Modules\Attendance\Actions\Concerns\RecomputesAttendanceSummary;
use Modules\Attendance\DTOs\MarkAttendanceDTO;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class MarkAttendanceAction extends BaseAction
{
    use RecomputesAttendanceSummary;

    public function __construct(
        protected AttendanceRepositoryInterface $repository,
    ) {}

    /**
     * @param  MarkAttendanceDTO  $input
     */
    public function execute(mixed $input): AttendanceRecord
    {
        /** @var MarkAttendanceDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): AttendanceRecord {
            $record = $this->repository->findRecordByClassDateSession($dto->classId, $dto->date, $dto->session);

            if ($record) {
                $record = $this->repository->updateRecord($record, [
                    'subject_id' => $dto->subjectId,
                    'academic_year_id' => $dto->academicYearId,
                    'semester_id' => $dto->semesterId,
                ]);
            } else {
                $record = $this->repository->createRecord([
                    ...$dto->toRecordArray(),
                    'created_by' => auth()->id(),
                ]);
            }

            $this->repository->syncDetails($record, $dto->students);

            $counts = $this->countStatuses($record->id);

            $record = $this->repository->updateRecord($record, [
                'present_count' => $counts['present'],
                'absent_count' => $counts['absent'],
                'late_count' => $counts['late'],
                'excused_count' => $counts['excused'],
            ]);

            foreach (array_column($dto->students, 'student_id') as $studentId) {
                $this->recomputeSummary($studentId, $record->academic_year_id, $record->semester_id, $record->class_id);
            }

            return $record->load('details.student.user');
        });
    }

    /**
     * @return array{present: int, absent: int, late: int, excused: int}
     */
    protected function countStatuses(int $recordId): array
    {
        $details = $this->repository->detailsForRecord($recordId);

        return [
            'present' => $details->where('status', 'present')->count(),
            'absent' => $details->where('status', 'absent')->count(),
            'late' => $details->where('status', 'late')->count(),
            'excused' => $details->where('status', 'excused')->count(),
        ];
    }
}
