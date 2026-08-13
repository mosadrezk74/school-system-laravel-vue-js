<?php

namespace Modules\Student\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Student\Http\Requests\Concerns\StudentProfileRules;

class UpdateStudentRequest extends BaseRequest
{
    use AccountFieldRules;
    use StudentProfileRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $student = $this->route('student');
        $userId = $student?->user_id;

        return [
            /*
             * Same shape as the store request, with this student's own rows
             * excluded from the unique checks so saving an unchanged email,
             * username, phone, national id or admission number is not reported
             * as a duplicate.
             */
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'username' => $this->usernameRules($userId),

            // Entered once, like the create form. Left blank it is ignored.
            'password' => ['nullable', 'string', 'min:8', 'max:255'],

            'gender' => $this->genderRules(),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE, required: true),
            'national_id' => $this->nationalIdRules($userId),
            'phone' => $this->phoneRules($userId),
            'is_active' => ['sometimes', 'boolean'],

            'admission_number' => [
                'nullable', 'string', 'max:'.self::ADMISSION_NUMBER_MAX,
                Rule::unique('student_profiles', 'admission_number')->ignore($student?->id),
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
