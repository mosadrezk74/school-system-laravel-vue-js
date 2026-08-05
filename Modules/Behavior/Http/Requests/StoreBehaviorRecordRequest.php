<?php

namespace Modules\Behavior\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreBehaviorRecordRequest extends BaseRequest
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
        ];
    }
}
