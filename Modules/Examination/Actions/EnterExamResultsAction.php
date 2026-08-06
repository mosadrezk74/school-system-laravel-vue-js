<?php

namespace Modules\Examination\Actions;

use Illuminate\Support\Collection;
use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\EnterExamResultsDTO;
use Modules\Examination\Exceptions\ExamOperationException;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Repositories\ExamResultRepositoryInterface;
use Modules\SchoolClass\Models\SchoolClass;

class EnterExamResultsAction extends BaseAction
{
    public function __construct(
        protected ExamResultRepositoryInterface $repository,
    ) {}

    /**
     * @param  EnterExamResultsDTO  $input
     * @return Collection<int, ExamResult>
     */
    public function execute(mixed $input): Collection
    {
        /** @var EnterExamResultsDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Collection {
            $examSubject = ExamSubject::query()
                ->where('exam_id', $dto->examId)
                ->where('subject_id', $dto->subjectId)
                ->where('class_id', $dto->classId)
                ->first();

            if (! $examSubject) {
                throw ExamOperationException::examSubjectNotConfigured();
            }

            $gradeLevelId = SchoolClass::query()->whereKey($dto->classId)->value('grade_level_id');

            $rows = array_map(function (array $row) use ($examSubject, $gradeLevelId, $dto): array {
                $obtainedMarks = $row['obtained_marks'] !== null ? (float) $row['obtained_marks'] : null;
                $percentage = $obtainedMarks !== null
                    ? round($obtainedMarks / (float) $examSubject->total_marks * 100, 2)
                    : null;
                $isPassed = $obtainedMarks !== null
                    ? $obtainedMarks >= (float) $examSubject->passing_marks
                    : null;

                return [
                    'student_id' => (int) $row['student_id'],
                    'obtained_marks' => $obtainedMarks,
                    'remarks' => $row['remarks'] ?? null,
                    'percentage' => $percentage,
                    'is_passed' => $isPassed,
                    'grade' => $percentage !== null
                        ? $this->resolveGrade($gradeLevelId, $dto->subjectId, $percentage)
                        : null,
                ];
            }, $dto->results);

            $results = $this->repository->syncResults($dto->examId, $dto->subjectId, $dto->classId, $rows, auth()->id());

            $this->recomputeRanks($dto->examId, $dto->subjectId, $dto->classId);

            return $this->repository->findByExamSubjectClass($dto->examId, $dto->subjectId, $dto->classId);
        });
    }

    protected function resolveGrade(?int $gradeLevelId, int $subjectId, float $percentage): ?string
    {
        if (! $gradeLevelId) {
            return null;
        }

        $grade = ExamGrade::query()
            ->where('grade_level_id', $gradeLevelId)
            ->where('subject_id', $subjectId)
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first()
            ?? ExamGrade::query()
                ->where('grade_level_id', $gradeLevelId)
                ->whereNull('subject_id')
                ->where('min_percentage', '<=', $percentage)
                ->where('max_percentage', '>=', $percentage)
                ->first();

        return $grade?->grade_name;
    }

    protected function recomputeRanks(int $examId, int $subjectId, int $classId): void
    {
        $ranked = $this->repository->findByExamSubjectClass($examId, $subjectId, $classId)
            ->whereNotNull('percentage')
            ->sortByDesc('percentage')
            ->values();

        foreach ($ranked as $index => $result) {
            $this->repository->updateRank($result->id, $index + 1);
        }
    }
}
