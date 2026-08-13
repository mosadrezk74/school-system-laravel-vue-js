<?php

namespace Modules\GuardianProfile\Http\Requests;

use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;

class UpdateGuardianRequest extends BaseRequest
{
    use AccountFieldRules;

    /** A guardian is an adult, and `parent_profiles.occupation` is varchar(255). */
    private const MIN_AGE = 18;

    private const MAX_AGE = 120;

    private const OCCUPATION_MAX = 255;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('guardian')?->user_id;

        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'username' => $this->usernameRules($userId),

            // Blank leaves the current password in place.
            'password' => ['nullable', 'string', 'min:8', 'max:255'],

            'gender' => $this->genderRules(required: false),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'phone' => $this->phoneRules($userId),
            'national_id' => $this->nationalIdRules($userId),
            'is_active' => ['sometimes', 'boolean'],
            'occupation' => ['nullable', 'string', 'max:'.self::OCCUPATION_MAX],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('guardian::guardian.fields.name'),
            'email' => __('guardian::guardian.fields.email'),
            'username' => __('guardian::guardian.fields.username'),
            'password' => __('guardian::guardian.fields.password'),
            'phone' => __('guardian::guardian.fields.phone'),
            'occupation' => __('guardian::guardian.fields.occupation'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->accountFieldMessages();
    }
}
