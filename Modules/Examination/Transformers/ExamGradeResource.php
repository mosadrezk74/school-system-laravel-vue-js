<?php

namespace Modules\Examination\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Examination\Models\ExamGrade;

/**
 * @mixin ExamGrade
 */
class ExamGradeResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_level_id' => $this->grade_level_id,
            'subject_id' => $this->subject_id,
            'grade_name' => $this->grade_name,
            'grade_point' => $this->grade_point,
            'min_percentage' => $this->min_percentage,
            'max_percentage' => $this->max_percentage,
            'is_passing' => $this->is_passing,

            'grade_level' => $this->whenLoaded('gradeLevel', fn () => $this->gradeLevel ? [
                'id' => $this->gradeLevel->id,
                'name' => $this->gradeLevel->name,
            ] : null),

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),
        ];
    }
}
