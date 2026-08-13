<?php

namespace Modules\Behavior\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreBehaviorRecordRequest extends BaseRequest
{
    /** `behavior_records.description` is a text column, so this is a domain limit. */
    private const DESCRIPTION_MAX = 1000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'behavior_category_id' => ['required', 'integer', 'exists:behavior_categories,id'],
            // An incident is recorded after it happens, and `description` is a text
            // column that previously had no cap at all.
            'date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:'.self::DESCRIPTION_MAX],
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
