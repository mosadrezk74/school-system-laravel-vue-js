<?php

namespace Modules\Teacher\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateTeacherRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('teacher').',id'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$this->route('teacher').',id'],
            'password' => ['nullable', 'string', 'min:8'],
            'gender' => ['required', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:teacher_profiles,employee_id,'.$this->route('teacher').',id'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'joining_date' => ['nullable', 'date'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'salary_type' => ['nullable', 'string', 'in:fixed,hourly,contract'],
            'salary_base' => ['nullable', 'numeric', 'min:0'],
            'primary_subjects' => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('teacher::teacher.fields.name'),
            'email' => __('teacher::teacher.fields.email'),
            'username' => __('teacher::teacher.fields.username'),
            'password' => __('teacher::teacher.fields.password'),
            'gender' => __('teacher::teacher.fields.gender'),
            'employee_id' => __('teacher::teacher.fields.employee_id'),
            'department_id' => __('teacher::teacher.fields.department_id'),
        ];
    }
}
