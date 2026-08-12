<?php

namespace Modules\Examination\DTOs;

/**
 * A student's consolidated results for one academic year + semester.
 *
 * Derived on read rather than stored: nothing in the domain freezes a report
 * card, and persisting one would mean a second copy of marks that could drift
 * from exam_results whenever a teacher corrects an entry.
 */
final readonly class ReportCardDTO
{
    /**
     * @param  array<int, ReportCardSubjectDTO>  $subjects
     */
    public function __construct(
        public int $studentId,
        public string $studentName,
        public ?string $admissionNumber,
        public ?int $classId,
        public ?string $className,
        public int $academicYearId,
        public ?string $academicYearName,
        public int $semesterId,
        public ?string $semesterName,
        public array $subjects,
        public ?float $totalObtained,
        public ?float $totalMax,
        public ?float $averagePercentage,
        public ?string $overallGrade,
        public ?int $classRank,
        public ?int $classSize,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'student_name' => $this->studentName,
            'admission_number' => $this->admissionNumber,
            'class_id' => $this->classId,
            'class_name' => $this->className,
            'academic_year_id' => $this->academicYearId,
            'academic_year_name' => $this->academicYearName,
            'semester_id' => $this->semesterId,
            'semester_name' => $this->semesterName,
            'subjects' => array_map(
                static fn (ReportCardSubjectDTO $subject): array => $subject->toArray(),
                $this->subjects,
            ),
            'total_obtained' => $this->totalObtained,
            'total_max' => $this->totalMax,
            'average_percentage' => $this->averagePercentage,
            'overall_grade' => $this->overallGrade,

            // Standing across the whole class for this academic context.
            'class_rank' => $this->classRank,
            'class_size' => $this->classSize,

            // Distinguishes "nothing marked yet" from a genuine zero.
            'has_results' => $this->totalObtained !== null,
        ];
    }
}
