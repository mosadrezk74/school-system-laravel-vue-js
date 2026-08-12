<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Examination\Models\ExamResult;

/**
 * Positions students against their classmates.
 *
 * Scope follows the existing schema: exam_results.rank has always been the
 * standing within one exam + subject + class, and that is left unchanged. The
 * report card computes an overall standing separately, over the same class and
 * academic context, using the same rules below.
 *
 * Ranking is "competition" style (1, 2, 2, 4): equal scores share the higher
 * position and the next distinct score skips the positions consumed by the
 * tie. The previous implementation sorted by percentage and assigned
 * index + 1, so two students on 90% received ranks 1 and 2 — an arbitrary
 * winner decided by row order, which also made the result non-reproducible
 * between runs.
 */
class ResultRankCalculator
{
    /**
     * Ranks keyed by the collection key of each entry.
     *
     * Ties are broken deterministically for *ordering* by the tiebreaker
     * callback (ascending), but tied scores still receive the same rank —
     * the tiebreaker only guarantees a stable, reproducible sequence.
     *
     * @template TKey of array-key
     *
     * @param  Collection<TKey, mixed>  $entries
     * @param  callable(mixed): float|null  $score  Null scores are unranked.
     * @param  callable(mixed): (int|string)  $tiebreaker  Stable secondary sort.
     * @return array<TKey, int>
     */
    public function rank(Collection $entries, callable $score, callable $tiebreaker): array
    {
        $scored = $entries
            ->filter(fn ($entry): bool => $score($entry) !== null)
            ->sortBy($tiebreaker)
            ->sortByDesc($score);

        $ranks = [];
        $position = 0;
        $seen = 0;
        $previous = null;

        foreach ($scored as $key => $entry) {
            $seen++;
            $value = (float) $score($entry);

            // A new distinct score takes the position after everyone already
            // seen; an equal score repeats the position just assigned.
            if ($previous === null || abs($value - $previous) > 0.0001) {
                $position = $seen;
                $previous = $value;
            }

            $ranks[$key] = $position;
        }

        return $ranks;
    }

    /**
     * Ranks exam results by percentage, breaking ordering ties on student id
     * so the sequence is identical on every run.
     *
     * @param  Collection<int, ExamResult>  $results
     * @return array<int, int> Rank keyed by exam result id.
     */
    public function rankResults(Collection $results): array
    {
        return $this->rank(
            $results->keyBy(fn (ExamResult $result): int => $result->id),
            fn (ExamResult $result): ?float => $result->percentage !== null
                ? (float) $result->percentage
                : null,
            fn (ExamResult $result): int => $result->student_id,
        );
    }
}
