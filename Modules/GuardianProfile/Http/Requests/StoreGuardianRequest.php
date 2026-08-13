<?php

namespace Modules\GuardianProfile\Http\Requests;

use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;

class StoreGuardianRequest extends BaseRequest
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
        return [
            /*
             * Limits come from AccountFieldRules so they match the `users`
             * columns — the username rule here used to allow 255 characters
             * into a varchar(100), and the phone was neither format-checked
             * nor checked against the column's unique index.
             */
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'username' => $this->usernameRules(),
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'gender' => $this->genderRules(required: false),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'phone' => $this->phoneRules(),
            'national_id' => $this->nationalIdRules(),
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
