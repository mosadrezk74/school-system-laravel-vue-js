<?php

namespace Modules\Assignment\Transformers;

use Illuminate\Http\Request;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin AssignmentSubmission
 */
class AssignmentSubmissionResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'student_id' => $this->student_id,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'score' => $this->score,
            'grade' => $this->grade,
            'feedback' => $this->feedback,
            'is_late' => $this->is_late,
            'is_graded' => $this->is_graded,

            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
            ] : null),
        ];
    }
}
