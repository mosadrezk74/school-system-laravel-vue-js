<?php

namespace Modules\StudyMaterial\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\StudyMaterial\Enums\StudyResourceType;

class UpdateStudyResourceRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('title'),
            ...$this->translatableRules('description', false),
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', Rule::in(StudyResourceType::values())],
            'external_url' => ['nullable', 'string', 'url', 'max:2048'],
            'file' => ['nullable', 'file', 'max:51200', 'mimes:'.implode(',', StoreStudyResourceRequest::ALLOWED_EXTENSIONS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('studymaterial::study_resource.fields.title'),
            'description' => __('studymaterial::study_resource.fields.description'),
            'subject_id' => __('studymaterial::study_resource.fields.subject_id'),
            'grade_level_id' => __('studymaterial::study_resource.fields.grade_level_id'),
            'classroom_id' => __('studymaterial::study_resource.fields.classroom_id'),
            'teacher_id' => __('studymaterial::study_resource.fields.teacher_id'),
            'type' => __('studymaterial::study_resource.fields.type'),
            'external_url' => __('studymaterial::study_resource.fields.external_url'),
            'file' => __('studymaterial::study_resource.fields.file'),
        ];
    }
}
