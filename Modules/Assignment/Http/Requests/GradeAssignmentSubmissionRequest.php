<?php

namespace Modules\Assignment\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class GradeAssignmentSubmissionRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'score' => ['required', 'numeric', 'min:0'],
            'grade' => ['nullable', 'string', 'max:10'],
            'feedback' => ['nullable', 'string'],
        ];
    }
}
