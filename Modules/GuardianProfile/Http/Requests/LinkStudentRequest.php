<?php

namespace Modules\GuardianProfile\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\GuardianProfile\Enums\RelationshipType;

class LinkStudentRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('student_profiles', 'id')->whereNull('deleted_at'),
            ],
            /*
             * A fixed set rather than 50 characters of free text: the same
             * person was previously filed as "Father", "father" and "dad"
             * across different links, which made the relationship unusable for
             * anything but display.
             */
            'relationship' => ['required', 'string', Rule::enum(RelationshipType::class)],
            'is_primary_contact' => ['sometimes', 'boolean'],
            'can_pickup' => ['sometimes', 'boolean'],
            'receives_notifications' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => __('guardian::guardian.fields.student_id'),
            'relationship' => __('guardian::guardian.fields.relationship'),
        ];
    }
}
