<?php

namespace Modules\StudyMaterial\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\StudyMaterial\Models\StudyResource;

/**
 * @mixin StudyResource
 */
class StudyResourceResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->file();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'subject_id' => $this->subject_id,
            'grade_level_id' => $this->grade_level_id,
            'classroom_id' => $this->classroom_id,
            'teacher_id' => $this->teacher_id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'external_url' => $this->external_url,
            'downloads_count' => $this->downloads_count,
            'created_at' => $this->created_at?->toIso8601String(),

            'file_name' => $media?->file_name,
            'file_size' => $media?->size,
            'file_mime_type' => $media?->mime_type,
            'has_file' => $media !== null,
            'uploaded_at' => $media?->created_at?->toIso8601String(),

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),

            'grade_level' => $this->whenLoaded('gradeLevel', fn () => $this->gradeLevel ? [
                'id' => $this->gradeLevel->id,
                'name' => $this->gradeLevel->name,
            ] : null),

            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null),

            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
            ] : null),
        ];
    }
}
