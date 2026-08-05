<?php

namespace Modules\Subject\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateSubjectRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:subjects,code,' . $this->route('subject') . ',id'],
            'name' => ['required', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
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
