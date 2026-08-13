<?php

namespace Modules\Student\Http\Requests;

use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Student\Http\Requests\Concerns\StudentProfileRules;

class StoreStudentRequest extends BaseRequest
{
    use AccountFieldRules;
    use StudentProfileRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * Account. These all write to `users`, so their limits come from
             * AccountFieldRules, which pins each one to its column width.
             *
             * The password is entered once. Staff create this account on the
             * student's behalf and hand the credentials over, so a second
             * confirmation box guards against a typo the student would never
             * have made — and the form only ever rendered one field, which
             * left `confirmed` rejecting every submission.
             */
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'username' => $this->usernameRules(),
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'gender' => $this->genderRules(),

            /*
             * A student must be a plausible school age: born at least 2 years
             * ago and no more than 100. `before:today` alone would accept a
             * newborn or a 1900 date.
             */
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE, required: true),

            'national_id' => $this->nationalIdRules(),
            'phone' => $this->phoneRules(),
            'is_active' => ['sometimes', 'boolean'],

            'admission_number' => [
                'nullable', 'string', 'max:'.self::ADMISSION_NUMBER_MAX,
                'unique:student_profiles,admission_number',
            ],

            ...$this->studentProfileRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->studentAttributes();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->studentMessages();
    }
}
