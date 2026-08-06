<?php

namespace Modules\Subject\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreSubjectRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50', 'unique:subjects,code'],
            ...$this->translatableRules('name'),
            ...$this->translatableRules('description', required: false),
            'short_name' => ['nullable', 'string', 'max:50'],
            'subject_type' => ['required', 'string', 'in:core,elective,extracurricular'],
            'pass_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'credit_hours' => ['nullable', 'integer', 'min:0', 'max:10'],
            'max_marks' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('subject::subject.fields.name'),
            'code' => __('subject::subject.fields.code'),
            'subject_type' => __('subject::subject.fields.subject_type'),
        ];
    }
}
