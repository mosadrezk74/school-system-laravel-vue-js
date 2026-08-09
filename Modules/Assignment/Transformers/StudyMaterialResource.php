<?php

namespace Modules\Assignment\Transformers;

use Illuminate\Http\Request;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin StudyMaterial
 */
class StudyMaterialResource extends BaseResource
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
            'title_translations' => $this->getTranslations('title'),
            'description' => $this->description,
            'description_translations' => $this->getTranslations('description'),
            'material_type' => $this->material_type,
            'is_published' => $this->is_published,
            'view_count' => $this->view_count,
            'download_count' => $this->download_count,
            'created_at' => $this->created_at?->toIso8601String(),

            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
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
        ];
    }
}
