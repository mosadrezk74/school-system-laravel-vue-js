<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Examination\DTOs\ReportCardDTO;
use Modules\Examination\DTOs\ReportCardResultDTO;
use Modules\Examination\DTOs\ReportCardSubjectDTO;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Services\GradeBandResolver;
use Modules\Examination\Services\ResultRankCalculator;
use Modules\Student\Models\StudentProfile;

/**
 * Reads a student's consolidated results for one academic year + semester.
 *
 * The report card is derived here rather than stored. Every number on it comes
 * from exam_results and exam_subjects, so a corrected mark is reflected the
 * next time the card is read, with no second copy to reconcile.
 *
 * `publishedOnly` is the same rule ExamResultQuery applies to the portal: a
 * family sees a subject's marks only once the school has released the exam.
 * Staff read everything, because reviewing before publishing is the workflow.
 */
class ReportCardQuery
{
    private bool $publishedOnly = false;

    public function __construct(
        private readonly GradeBandResolver $gradeBands,
        private readonly ResultRankCalculator $ranks,
    ) {}

    /** Restricts every figure on the card to results from published exams. */
    public function publishedOnly(bool $publishedOnly = true): self
    {
        $this->publishedOnly = $publishedOnly;

        return $this;
    }

    /**
     * @param  int  $academicYearId  Resolved by the caller from
     *                               AcademicContextService, never from input.
     */
    public function forStudent(StudentProfile $student, int $academicYearId, int $semesterId): ReportCardDTO
    {
        $student->loadMissing(['user', 'currentClass.gradeLevel']);

        $results = $this->resultsFor($student->id, $academicYearId, $semesterId);
        $examSubjects = $this->examSubjectsFor($student->current_class_id, $academicYearId, $semesterId);

        $gradeLevelId = $student->currentClass?->grade_level_id;
        $subjects = $this->buildSubjects($results, $examSubjects, $gradeLevelId);

        [$totalObtained, $totalMax] = $this->totals($subjects);

        $average = $totalMax !== null && $totalMax > 0.0
            ? round($totalObtained / $totalMax * 100, 2)
            : null;

        return new ReportCardDTO(
            studentId: $student->id,
            studentName: (string) $student->user?->name,
            admissionNumber: $student->admission_number,
            classId: $student->current_class_id,
            className: $student->currentClass?->name,
            academicYearId: $academicYearId,
            academicYearName: $this->academicYearName($academicYearId),
            semesterId: $semesterId,
            semesterName: $this->semesterName($semesterId),
            subjects: $subjects,
            totalObtained: $totalObtained,
            totalMax: $totalMax,
            averagePercentage: $average,
            overallGrade: $this->gradeBands->resolveName($gradeLevelId, null, $average),
            classRank: null,
            classSize: null,
        );
    }

    /**
     * The card with the student's standing among classmates filled in.
     *
     * Kept separate because ranking costs a pass over every classmate's
     * results, which a single-student view does not always need.
     */
    public function forStudentWithRank(StudentProfile $student, int $academicYearId, int $semesterId): ReportCardDTO
    {
        $card = $this->forStudent($student, $academicYearId, $semesterId);

        if ($student->current_class_id === null) {
            return $card;
        }

        $averages = $this->classAverages($student->current_class_id, $academicYearId, $semesterId);

        $ranked = $this->ranks->rank(
            collect($averages),
            fn (array $row): ?float => $row['average'],
            fn (array $row): int => $row['student_id'],
        );

        return new ReportCardDTO(
            studentId: $card->studentId,
            studentName: $card->studentName,
            admissionNumber: $card->admissionNumber,
            classId: $card->classId,
            className: $card->className,
            academicYearId: $card->academicYearId,
            academicYearName: $card->academicYearName,
            semesterId: $card->semesterId,
            semesterName: $card->semesterName,
            subjects: $card->subjects,
            totalObtained: $card->totalObtained,
            totalMax: $card->totalMax,
            averagePercentage: $card->averagePercentage,
            overallGrade: $card->overallGrade,
            classRank: $ranked[$student->id] ?? null,
            classSize: count($averages),
        );
    }

    /**
     * Each classmate's overall average for the same context — the basis for
     * the class standing. Students with no marks yet are carried with a null
     * average so they count towards the class size but stay unranked.
     *
     * @return array<int, array{student_id: int, average: float|null}>
     */
    private function classAverages(int $classId, int $academicYearId, int $semesterId): array
    {
        $studentIds = StudentProfile::query()
            ->where('current_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return [];
        }

        $results = $this->baseResultQuery($academicYearId, $semesterId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        $examSubjects = $this->examSubjectsFor($classId, $academicYearId, $semesterId);

        return $studentIds
            ->map(function (int $studentId) use ($results, $examSubjects): array {
                $marks = $this->weighted($results->get($studentId, collect()), $examSubjects);

                return [
                    'student_id' => $studentId,
                    'average' => $marks['max'] > 0.0
                        ? round($marks['obtained'] / $marks['max'] * 100, 2)
                        : null,
                ];
            })
            ->keyBy('student_id')
            ->all();
    }

    /**
     * Obtained and available marks across every graded result.
     *
     * @param  Collection<int, ExamResult>  $results
     * @param  Collection<string, ExamSubject>  $examSubjects
     * @return array{obtained: float, max: float}
     */
    private function weighted(Collection $results, Collection $examSubjects): array
    {
        $obtained = 0.0;
        $max = 0.0;

        foreach ($results as $result) {
            if ($result->obtained_marks === null) {
                continue;
            }

            $total = $this->totalMarksFor($examSubjects, $result);

            if ($total === null) {
                continue;
            }

            $obtained += (float) $result->obtained_marks;
            $max += $total;
        }

        return ['obtained' => $obtained, 'max' => $max];
    }

    /**
     * @param  Collection<int, ExamResult>  $results
     * @param  Collection<string, ExamSubject>  $examSubjects
     * @return array<int, ReportCardSubjectDTO>
     */
    private function buildSubjects(Collection $results, Collection $examSubjects, ?int $gradeLevelId): array
    {
        return $results
            ->groupBy('subject_id')
            ->map(function (Collection $subjectResults, int $subjectId) use ($examSubjects, $gradeLevelId): ReportCardSubjectDTO {
                $lines = $subjectResults
                    ->map(fn (ExamResult $result): ReportCardResultDTO => new ReportCardResultDTO(
                        resultId: $result->id,
                        examId: $result->exam_id,
                        examName: (string) $result->exam?->name,
                        obtainedMarks: $result->obtained_marks !== null ? (float) $result->obtained_marks : null,
                        maxMarks: $this->totalMarksFor($examSubjects, $result),
                        percentage: $result->percentage !== null ? (float) $result->percentage : null,
                        grade: $result->grade,
                        isPassed: $result->is_passed,
                        rank: $result->rank,
                        isPublished: (bool) $result->exam?->is_published,
                    ))
                    ->values()
                    ->all();

                $marks = $this->weighted($subjectResults, $examSubjects);
                $hasMarks = $marks['max'] > 0.0;

                $percentage = $hasMarks
                    ? round($marks['obtained'] / $marks['max'] * 100, 2)
                    : null;

                return new ReportCardSubjectDTO(
                    subjectId: $subjectId,
                    subjectName: (string) $subjectResults->first()?->subject?->name,
                    results: $lines,
                    obtainedMarks: $hasMarks ? round($marks['obtained'], 2) : null,
                    maxMarks: $hasMarks ? round($marks['max'], 2) : null,
                    percentage: $percentage,
                    grade: $this->gradeBands->resolveName($gradeLevelId, $subjectId, $percentage),

                    // Passing the subject overall means passing every sitting
                    // that has been marked, not just the last one.
                    isPassed: $hasMarks
                        ? ! $subjectResults->contains(fn (ExamResult $r): bool => $r->is_passed === false)
                        : null,
                );
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, ReportCardSubjectDTO>  $subjects
     * @return array{0: float|null, 1: float|null}
     */
    private function totals(array $subjects): array
    {
        $obtained = null;
        $max = null;

        foreach ($subjects as $subject) {
            if ($subject->obtainedMarks === null || $subject->maxMarks === null) {
                continue;
            }

            $obtained = ($obtained ?? 0.0) + $subject->obtainedMarks;
            $max = ($max ?? 0.0) + $subject->maxMarks;
        }

        return [
            $obtained !== null ? round($obtained, 2) : null,
            $max !== null ? round($max, 2) : null,
        ];
    }

    /**
     * @return Collection<int, ExamResult>
     */
    private function resultsFor(int $studentId, int $academicYearId, int $semesterId): Collection
    {
        return $this->baseResultQuery($academicYearId, $semesterId)
            ->where('student_id', $studentId)
            ->with(['subject', 'exam'])
            ->get();
    }

    /**
     * Results are tied to an academic context only through their exam, since
     * exam_results carries no academic_year_id of its own.
     *
     * @return Builder<ExamResult>
     */
    private function baseResultQuery(int $academicYearId, int $semesterId): Builder
    {
        return ExamResult::query()
            ->with(['exam', 'subject'])
            ->whereHas('exam', function (Builder $query) use ($academicYearId, $semesterId): void {
                $query->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);

                if ($this->publishedOnly) {
                    $query->where('is_published', true);
                }
            });
    }

    /**
     * Per-exam marks available for a class, keyed "examId:subjectId" so a
     * result can find its own denominator without another query.
     *
     * @return Collection<string, ExamSubject>
     */
    private function examSubjectsFor(?int $classId, int $academicYearId, int $semesterId): Collection
    {
        if ($classId === null) {
            return collect();
        }

        return ExamSubject::query()
            ->where('class_id', $classId)
            ->whereHas('exam', function (Builder $query) use ($academicYearId, $semesterId): void {
                $query->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);

                if ($this->publishedOnly) {
                    $query->where('is_published', true);
                }
            })
            ->get()
            ->keyBy(fn (ExamSubject $examSubject): string => "{$examSubject->exam_id}:{$examSubject->subject_id}");
    }

    /**
     * @param  Collection<string, ExamSubject>  $examSubjects
     */
    private function totalMarksFor(Collection $examSubjects, ExamResult $result): ?float
    {
        $examSubject = $examSubjects->get("{$result->exam_id}:{$result->subject_id}");

        return $examSubject !== null ? (float) $examSubject->total_marks : null;
    }

    /**
     * Read from the reported year itself.
     *
     * It used to come from the student's class, but classes no longer carry an
     * academic year — and the card's year is the one being reported on anyway,
     * which is the more truthful source.
     */
    private function academicYearName(int $academicYearId): ?string
    {
        return AcademicYear::query()->whereKey($academicYearId)->value('name');
    }

    /**
     * Resolved through the model rather than value('name'): the column is
     * Spatie-translatable JSON, so a raw read would hand the page the whole
     * locale map instead of the active language's text.
     */
    private function semesterName(int $semesterId): ?string
    {
        return Semester::query()->whereKey($semesterId)->first()?->name;
    }
}
