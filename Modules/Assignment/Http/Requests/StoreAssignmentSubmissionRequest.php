<?php

namespace Modules\Assignment\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreAssignmentSubmissionRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            // A submission cannot be recorded as having happened in the future.
            'submitted_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }
}
