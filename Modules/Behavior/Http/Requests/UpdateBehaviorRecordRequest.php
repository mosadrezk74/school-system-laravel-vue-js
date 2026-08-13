<?php

namespace Modules\Behavior\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Behavior\Enums\BehaviorRecordStatus;
use Modules\Core\Abstracts\BaseRequest;

class UpdateBehaviorRecordRequest extends BaseRequest
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
            'status' => ['required', 'string', Rule::enum(BehaviorRecordStatus::class)],
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
