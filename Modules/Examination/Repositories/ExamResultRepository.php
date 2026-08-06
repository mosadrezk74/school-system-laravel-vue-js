<?php

namespace Modules\Examination\Repositories;

use Illuminate\Support\Collection;
use Modules\Examination\Models\ExamResult;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks
 * (percentage/grade/rank computation lives in EnterExamResultsAction).
 */
class ExamResultRepository implements ExamResultRepositoryInterface
{
    public function findByExamSubjectClass(int $examId, int $subjectId, int $classId): Collection
    {
        return ExamResult::query()
            ->where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->get();
    }

    public function syncResults(int $examId, int $subjectId, int $classId, array $rows, ?int $actorId = null): Collection
    {
        $results = new Collection;

        foreach ($rows as $row) {
            $results->push(ExamResult::updateOrCreate(
                [
                    'exam_id' => $examId,
                    'subject_id' => $subjectId,
                    'student_id' => $row['student_id'],
                ],
                [
                    'class_id' => $classId,
                    'obtained_marks' => $row['obtained_marks'],
                    'remarks' => $row['remarks'],
                    'percentage' => $row['percentage'],
                    'is_passed' => $row['is_passed'],
                    'grade' => $row['grade'],
                    'updated_by' => $actorId,
                ]
            ));
        }

        return $results;
    }

    public function updateRank(int $examResultId, int $rank): void
    {
        ExamResult::query()->whereKey($examResultId)->update(['rank' => $rank]);
    }
}
