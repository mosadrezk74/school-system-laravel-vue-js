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
            'submitted_at' => ['required', 'date'],
        ];
    }
}
