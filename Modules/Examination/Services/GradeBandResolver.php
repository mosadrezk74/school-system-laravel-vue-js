<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Examination\Models\ExamGrade;

/**
 * Turns a percentage into the school's grade letter.
 *
 * The bands themselves live in exam_grades and are configured per grade level,
 * optionally narrowed to one subject. This class is only the lookup: it does
 * not define grades, and there is no second grading model anywhere.
 *
 * Extracted from EnterExamResultsAction so the report card can label a
 * consolidated average with the same bands that labelled each individual
 * result — previously the logic was private to the Action and unreachable.
 */
class GradeBandResolver
{
    /**
     * Bands already loaded this request, keyed by grade level. A report card
     * resolves one grade per subject plus an overall grade, which would
     * otherwise be a query each.
     *
     * @var array<int, Collection<int, ExamGrade>>
     */
    private array $cache = [];

    /**
     * A subject-specific band wins over the grade level's general band, which
     * is how a school gives (say) mathematics a stricter scale than the rest.
     */
    public function resolve(?int $gradeLevelId, ?int $subjectId, ?float $percentage): ?ExamGrade
    {
        if ($gradeLevelId === null || $percentage === null) {
            return null;
        }

        $bands = $this->bandsFor($gradeLevelId);

        return $bands->first(fn (ExamGrade $band): bool => $band->subject_id === $subjectId
            && $this->covers($band, $percentage))
            ?? $bands->first(fn (ExamGrade $band): bool => $band->subject_id === null
                && $this->covers($band, $percentage));
    }

    public function resolveName(?int $gradeLevelId, ?int $subjectId, ?float $percentage): ?string
    {
        return $this->resolve($gradeLevelId, $subjectId, $percentage)?->grade_name;
    }

    /**
     * @return Collection<int, ExamGrade>
     */
    private function bandsFor(int $gradeLevelId)
    {
        return $this->cache[$gradeLevelId] ??= ExamGrade::query()
            ->where('grade_level_id', $gradeLevelId)
            ->orderBy('min_percentage')
            ->get();
    }

    /** Bounds are inclusive at both ends, matching the original lookup. */
    private function covers(ExamGrade $band, float $percentage): bool
    {
        return (float) $band->min_percentage <= $percentage
            && (float) $band->max_percentage >= $percentage;
    }
}
