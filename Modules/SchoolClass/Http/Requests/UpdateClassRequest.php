<?php

namespace Modules\SchoolClass\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateClassRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $class = $this->route('class');

        return [
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'name' => ['required', 'string', 'max:100'],
            'section_code' => [
                'required', 'string', 'max:10',
                Rule::unique('classes', 'section_code')->where(function ($query) {
                    return $query
                        ->where('academic_year_id', $this->input('academic_year_id'))
                        ->where('grade_level_id', $this->input('grade_level_id'));
                })->ignore($class?->id),
            ],
            'capacity' => ['required', 'integer', 'min:1'],
            'class_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'assistant_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'grade_level_id' => __('school-class::class.fields.grade_level_id'),
            'academic_year_id' => __('school-class::class.fields.academic_year_id'),
            'classroom_id' => __('school-class::class.fields.classroom_id'),
            'name' => __('school-class::class.fields.name'),
            'section_code' => __('school-class::class.fields.section_code'),
            'capacity' => __('school-class::class.fields.capacity'),
            'class_teacher_id' => __('school-class::class.fields.class_teacher_id'),
            'assistant_teacher_id' => __('school-class::class.fields.assistant_teacher_id'),
        ];
    }
}
