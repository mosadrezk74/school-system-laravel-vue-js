<?php

namespace Modules\Teacher\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Teacher\Models\TeacherProfile;

class UpdateTeacherRequest extends BaseRequest
{
    public function rules(): array
    {
        /** @var TeacherProfile|null $teacher */
        $teacher = $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher?->user_id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($teacher?->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'gender' => ['required', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'employee_id' => ['nullable', 'string', 'digits:7', Rule::unique('teacher_profiles', 'employee_id')->ignore($teacher?->id)],
            'qualification' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'joining_date' => ['nullable', 'date'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
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
            'subject_ids' => __('teacher::teacher.fields.subject_ids'),
        ];
    }
}
