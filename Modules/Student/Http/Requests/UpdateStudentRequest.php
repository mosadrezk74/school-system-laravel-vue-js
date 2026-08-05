<?php

namespace Modules\Student\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateStudentRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->user_id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($student?->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'gender' => ['required', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],

            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'current_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'admission_number' => [
                'nullable', 'string', 'max:50',
                Rule::unique('student_profiles', 'admission_number')->ignore($student?->id),
            ],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'admission_date' => ['required', 'date'],

            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],

            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],

            'blood_group' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],

            'previous_school' => ['nullable', 'string', 'max:255'],
            'scholarship_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_graduated' => ['sometimes', 'boolean'],
            'extra_fields' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('student::student.fields.name'),
            'email' => __('student::student.fields.email'),
            'username' => __('student::student.fields.username'),
            'password' => __('student::student.fields.password'),
            'gender' => __('student::student.fields.gender'),
            'academic_year_id' => __('student::student.fields.academic_year_id'),
            'current_class_id' => __('student::student.fields.current_class_id'),
            'admission_number' => __('student::student.fields.admission_number'),
            'admission_date' => __('student::student.fields.admission_date'),
        ];
    }
}
