<?php

namespace Modules\Student\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class StoreStudentRequest extends BaseRequest
{
    /**
     * The eight ABO/Rh groups. Kept as a whitelist rather than a length cap so
     * a typo like "A++" is rejected at the form instead of reaching a medical
     * record where it would be worse than no value at all.
     *
     * @var array<int, string>
     */
    public const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * Account. `regex` on the username keeps it URL- and login-safe;
             * `confirmed` pairs with the password_confirmation field, and
             * Password::default() applies the app's configured strength rules
             * instead of a bare min:8.
             */
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'username' => [
                'required', 'string', 'min:3', 'max:100',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'unique:users,username',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['required', 'string', 'in:male,female'],

            /*
             * A student must be a plausible school age: born at least 2 years
             * ago and no more than 100. `before:today` alone would accept a
             * newborn or a 1900 date.
             */
            'birth_date' => ['required', 'date', 'before:'.now()->subYears(2)->toDateString(), 'after:'.now()->subYears(100)->toDateString()],

            'national_id' => ['nullable', 'string', 'regex:/^[0-9]{8,20}$/', 'unique:users,national_id'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s-]{7,20}$/'],
            'is_active' => ['sometimes', 'boolean'],

            // Enrolment
            'current_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'admission_number' => ['nullable', 'string', 'max:50', 'unique:student_profiles,admission_number'],
            'roll_number' => ['nullable', 'string', 'max:50'],

            /*
             * Admission cannot be in the future, and cannot predate the
             * student's own birth — both are silently corrupting if allowed.
             */
            'admission_date' => ['required', 'date', 'before_or_equal:today', 'after:birth_date'],

            'is_graduated' => ['sometimes', 'boolean'],

            // Emergency contact — if a name is given the phone becomes required,
            // since a contact without a number is useless in an emergency.
            'emergency_contact_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'required_with:emergency_contact_name', 'string', 'regex:/^\+?[0-9\s-]{7,20}$/'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],

            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],

            // Family
            'father_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'father_phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s-]{7,20}$/'],
            'mother_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s-]{7,20}$/'],
            'guardian_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'guardian_phone' => ['nullable', 'required_with:guardian_name', 'string', 'regex:/^\+?[0-9\s-]{7,20}$/'],
            'guardian_relation' => ['nullable', 'required_with:guardian_name', 'string', 'max:50'],

            // Medical
            'blood_group' => ['nullable', 'string', Rule::in(self::BLOOD_GROUPS)],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'medical_conditions' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Trims every string and turns the blanks an HTML form submits for
     * untouched optional inputs into real nulls, so `nullable` rules are not
     * defeated by an empty string reaching a regex.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                $normalized[$key] = $trimmed === '' ? null : $trimmed;
            }
        }

        $this->merge($normalized);
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
            'current_class_id' => __('student::student.fields.current_class_id'),
            'admission_number' => __('student::student.fields.admission_number'),
            'admission_date' => __('student::student.fields.admission_date'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => __('student::student.validation.username_format'),
            'national_id.regex' => __('student::student.validation.national_id_format'),
            'phone.regex' => __('student::student.validation.phone_format'),
            'emergency_contact_phone.regex' => __('student::student.validation.phone_format'),
            'father_phone.regex' => __('student::student.validation.phone_format'),
            'mother_phone.regex' => __('student::student.validation.phone_format'),
            'guardian_phone.regex' => __('student::student.validation.phone_format'),
            'birth_date.before' => __('student::student.validation.birth_date_too_recent'),
            'birth_date.after' => __('student::student.validation.birth_date_too_old'),
            'admission_date.after' => __('student::student.validation.admission_before_birth'),
        ];
    }
}
