<?php

namespace Modules\Assignment\Transformers;

use Illuminate\Http\Request;
use Modules\Assignment\Models\Assignment;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin Assignment
 */
class AssignmentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'title' => $this->title,
            'description' => $this->description,
            'max_score' => $this->max_score,
            'deadline' => $this->deadline?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'assignment_type' => $this->assignment_type,
            'is_group_work' => $this->is_group_work,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at?->toIso8601String(),

            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
                'section_code' => $this->class->section_code,
            ] : null),

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),

            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
            ] : null),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'semester' => $this->whenLoaded('semester', fn () => $this->semester ? [
                'id' => $this->semester->id,
                'name' => $this->semester->name,
            ] : null),

            'submissions' => $this->whenLoaded('submissions', fn () => $this->submissions->map(fn ($submission) => [
                'id' => $submission->id,
                'student_id' => $submission->student_id,
                'student_name' => $submission->student?->user?->name,
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
                'is_late' => $submission->is_late,
                'is_graded' => $submission->is_graded,
                'score' => $submission->score,
                'grade' => $submission->grade,
                'feedback' => $submission->feedback,
            ])->all()),
        ];
    }
}
