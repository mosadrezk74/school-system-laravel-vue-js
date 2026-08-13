<?php

namespace Modules\Student\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Modules\Core\Rules\PhoneNumberRule;
use Modules\GuardianProfile\Enums\RelationshipType;
use Modules\Student\Enums\BloodGroup;

/**
 * The `student_profiles` half of the student form. The store and update
 * requests differ only in their uniqueness ignores, so everything that is
 * genuinely identical lives here instead of being maintained twice.
 */
trait StudentProfileRules
{
    /** Youngest and oldest a student may plausibly be, in years. */
    protected const MIN_AGE = 2;

    protected const MAX_AGE = 100;

    /** `student_profiles` column widths. */
    protected const ADMISSION_NUMBER_MAX = 50;

    protected const ROLL_NUMBER_MAX = 50;

    protected const CITY_MAX = 100;

    protected const CONTACT_NAME_MAX = 255;

    /**
     * `address`, `allergies` and `medical_conditions` are `text` columns, so
     * these caps are domain choices rather than the column's: long enough for
     * a real answer, short enough to stay a form field.
     */
    protected const ADDRESS_MAX = 500;

    protected const MEDICAL_NOTE_MAX = 1000;

    /**
     * Enrolment, family, address and medical fields — everything on the
     * student record that is not an account column.
     *
     * @return array<string, mixed>
     */
    protected function studentProfileRules(): array
    {
        return [
            'current_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'roll_number' => ['nullable', 'string', 'max:'.self::ROLL_NUMBER_MAX],

            /*
             * Admission cannot be in the future, and cannot predate the
             * student's own birth — both are silently corrupting if allowed.
             */
            'admission_date' => ['required', 'date', 'before_or_equal:today', 'after:birth_date'],

            'is_graduated' => ['sometimes', 'boolean'],

            /*
             * A share of the fee, so it cannot fall outside 0–100: a negative
             * value would bill above the fee's own price and over 100 would
             * produce a negative balance. StudentFeeCalculator clamps to the
             * same range, this rejects it at the form instead.
             */
            'scholarship_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            /*
             * Emergency contact — if a name is given the phone becomes
             * required, since a contact without a number is useless in an
             * emergency.
             */
            'emergency_contact_name' => ['nullable', 'string', 'min:2', 'max:'.self::CONTACT_NAME_MAX],
            'emergency_contact_phone' => $this->contactPhoneRules(requiredWith: 'emergency_contact_name'),
            'emergency_contact_relation' => ['nullable', 'string', Rule::enum(RelationshipType::class)],

            'address' => ['nullable', 'string', 'max:'.self::ADDRESS_MAX],
            'city' => ['nullable', 'string', 'max:'.self::CITY_MAX],

            // Family
            'father_name' => ['nullable', 'string', 'min:2', 'max:'.self::CONTACT_NAME_MAX],
            'father_phone' => $this->contactPhoneRules(),
            'mother_name' => ['nullable', 'string', 'min:2', 'max:'.self::CONTACT_NAME_MAX],
            'mother_phone' => $this->contactPhoneRules(),
            'guardian_name' => ['nullable', 'string', 'min:2', 'max:'.self::CONTACT_NAME_MAX],
            'guardian_phone' => $this->contactPhoneRules(requiredWith: 'guardian_name'),
            'guardian_relation' => [
                'nullable', 'required_with:guardian_name', 'string',
                Rule::enum(RelationshipType::class),
            ],

            /*
             * Medical. The blood group is a whitelist rather than a length cap
             * so a typo like "A++" is rejected at the form instead of reaching
             * a medical record, where it would be worse than no value at all.
             */
            'blood_group' => ['nullable', 'string', Rule::enum(BloodGroup::class)],
            'allergies' => ['nullable', 'string', 'max:'.self::MEDICAL_NOTE_MAX],
            'medical_conditions' => ['nullable', 'string', 'max:'.self::MEDICAL_NOTE_MAX],
        ];
    }

    /**
     * A next-of-kin phone stored on `student_profiles`. Same shape and width as
     * the account phone, but deliberately not unique: siblings share a parent's
     * number, and one guardian is the contact for every child they have here.
     *
     * @return array<int, mixed>
     */
    protected function contactPhoneRules(?string $requiredWith = null): array
    {
        return array_values(array_filter([
            'nullable',
            $requiredWith ? 'required_with:'.$requiredWith : null,
            'string',
            'max:'.self::PHONE_MAX,
            new PhoneNumberRule,
        ]));
    }

    /**
     * @return array<string, string>
     */
    protected function studentMessages(): array
    {
        return [
            'username.regex' => __('student::student.validation.username_format'),
            'national_id.regex' => __('student::student.validation.national_id_format'),
            'birth_date.before' => __('student::student.validation.birth_date_too_recent'),
            'birth_date.after' => __('student::student.validation.birth_date_too_old'),
            'admission_date.after' => __('student::student.validation.admission_before_birth'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function studentAttributes(): array
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
}
