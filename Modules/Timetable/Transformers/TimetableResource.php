<?php

namespace Modules\Timetable\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Timetable\Models\Timetable;

/**
 * @mixin Timetable
 */
class TimetableResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),

            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
                'section_code' => $this->class->section_code,
                'grade_level' => $this->class->relationLoaded('gradeLevel') ? [
                    'id' => $this->class->gradeLevel?->id,
                    'name' => $this->class->gradeLevel?->name,
                ] : null,
                'branch' => $this->class->relationLoaded('branch') ? [
                    'id' => $this->class->branch?->id,
                    'name' => $this->class->branch?->name,
                ] : null,
            ] : null),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'semester' => $this->whenLoaded('semester', fn () => $this->semester ? [
                'id' => $this->semester->id,
                'name' => $this->semester->name,
            ] : null),

            'entries' => $this->whenLoaded('entries', fn () => TimetableEntryResource::collection($this->entries)),
        ];
    }
}
