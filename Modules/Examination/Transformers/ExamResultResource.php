<?php

namespace Modules\Examination\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Examination\Models\ExamResult;

/**
 * @mixin ExamResult
 */
class ExamResultResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'obtained_marks' => $this->obtained_marks,
            'grade' => $this->grade,
            'is_passed' => $this->is_passed,
            'percentage' => $this->percentage,
            'rank' => $this->rank,
            'remarks' => $this->remarks,

            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'admission_number' => $this->student->admission_number,
            ] : null),
        ];
    }
}
