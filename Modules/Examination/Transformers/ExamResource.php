<?php

namespace Modules\Examination\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Examination\Models\Exam;

/**
 * @mixin Exam
 */
class ExamResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'class_id' => $this->class_id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'exam_type' => $this->exam_type,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'passing_grade' => $this->passing_grade,
            'total_marks' => $this->total_marks,
            'weightage' => $this->weightage,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at?->toIso8601String(),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'semester' => $this->whenLoaded('semester', fn () => $this->semester ? [
                'id' => $this->semester->id,
                'name' => $this->semester->name,
            ] : null),

            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
            ] : null),

            'exam_subjects' => $this->whenLoaded('examSubjects', fn () => $this->examSubjects->map(fn ($examSubject) => [
                'id' => $examSubject->id,
                'subject_id' => $examSubject->subject_id,
                'class_id' => $examSubject->class_id,
                'subject_name' => $examSubject->subject?->name,
                'class_name' => $examSubject->class?->name,
                'total_marks' => $examSubject->total_marks,
                'passing_marks' => $examSubject->passing_marks,
                'exam_date' => $examSubject->exam_date?->toDateString(),
                'exam_time' => $examSubject->exam_time,
                'room' => $examSubject->room,
            ])->all()),
        ];
    }
}
