<?php

namespace Modules\Behavior\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateBehaviorRecordRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'behavior_category_id' => ['required', 'integer', 'exists:behavior_categories,id'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:reported,reviewed'],
            'reviewed_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => __('behavior::behavior.fields.student'),
            'behavior_category_id' => __('behavior::behavior.fields.category'),
            'date' => __('behavior::behavior.fields.date'),
            'status' => __('behavior::behavior.fields.status'),
            'reviewed_by' => __('behavior::behavior.fields.reviewed_by'),
        ];
    }
}
