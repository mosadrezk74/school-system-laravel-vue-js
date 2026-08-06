<?php

namespace Modules\Examination\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Examination\Models\ExamSubject;

/**
 * @mixin ExamSubject
 */
class ExamSubjectResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'subject_id' => $this->subject_id,
            'class_id' => $this->class_id,
            'total_marks' => $this->total_marks,
            'passing_marks' => $this->passing_marks,
            'exam_date' => $this->exam_date?->toDateString(),
            'exam_time' => $this->exam_time,
            'room' => $this->room,

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),

            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
                'section_code' => $this->class->section_code,
            ] : null),
        ];
    }
}
