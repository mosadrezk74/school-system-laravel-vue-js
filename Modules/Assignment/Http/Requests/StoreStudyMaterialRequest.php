<?php

namespace Modules\Assignment\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreStudyMaterialRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material_type' => ['nullable', 'string', 'max:20'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'subject_id' => __('assignment::study_material.fields.subject_id'),
            'teacher_id' => __('assignment::study_material.fields.teacher_id'),
            'academic_year_id' => __('assignment::study_material.fields.academic_year_id'),
            'semester_id' => __('assignment::study_material.fields.semester_id'),
            'title' => __('assignment::study_material.fields.title'),
        ];
    }
}
