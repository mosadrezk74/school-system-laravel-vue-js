<?php

namespace Modules\SchoolClass\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\SchoolClass\Models\SchoolClass;

/**
 * @mixin SchoolClass
 */
class ClassResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'grade_level_id' => $this->grade_level_id,
            'academic_year_id' => $this->academic_year_id,
            'classroom_id' => $this->classroom_id,
            'name' => $this->name,
            'section_code' => $this->section_code,
            'capacity' => $this->capacity,
            'current_enrollment' => $this->current_enrollment,
            'class_teacher_id' => $this->class_teacher_id,
            'assistant_teacher_id' => $this->assistant_teacher_id,
            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->toIso8601String(),

            'grade_level' => $this->whenLoaded('gradeLevel', fn () => $this->gradeLevel ? [
                'id' => $this->gradeLevel->id,
                'name' => $this->gradeLevel->name,
            ] : null),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),

            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null),

            'class_teacher' => $this->whenLoaded('classTeacher', fn () => $this->classTeacher ? [
                'id' => $this->classTeacher->id,
                'name' => $this->classTeacher->name,
            ] : null),

            'assistant_teacher' => $this->whenLoaded('assistantTeacher', fn () => $this->assistantTeacher ? [
                'id' => $this->assistantTeacher->id,
                'name' => $this->assistantTeacher->name,
            ] : null),
        ];
    }
}
