<?php

namespace Modules\Academic\Transformers;

use Illuminate\Http\Request;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin Semester
 */
class SemesterResource extends BaseResource
{
    /**
     * `name` resolves to the active locale for display, while name_translations
     * carries every stored locale for the Show page.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'sequence' => $this->sequence,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'exam_start_date' => $this->exam_start_date?->toDateString(),
            'exam_end_date' => $this->exam_end_date?->toDateString(),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),
        ];
    }
}
