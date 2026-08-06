<?php

namespace Modules\Examination\Repositories;

use Illuminate\Support\Collection;
use Modules\Examination\Models\ExamResult;

interface ExamResultRepositoryInterface
{
    /**
     * @return Collection<int, ExamResult>
     */
    public function findByExamSubjectClass(int $examId, int $subjectId, int $classId): Collection;

    /**
     * @param  array<int, array{student_id: int, obtained_marks: ?float, remarks: ?string, percentage: ?float, is_passed: ?bool, grade: ?string}>  $rows
     * @return Collection<int, ExamResult>
     */
    public function syncResults(int $examId, int $subjectId, int $classId, array $rows, ?int $actorId = null): Collection;

    public function updateRank(int $examResultId, int $rank): void;
}
