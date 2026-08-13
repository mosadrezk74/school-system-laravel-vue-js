<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;

class StoreAdminRequest extends BaseRequest
{
    use AccountFieldRules;

    /** An administrator is a member of staff, so an adult age range applies. */
    private const MIN_AGE = 18;

    private const MAX_AGE = 100;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * Account fields come from AccountFieldRules so this screen keeps
             * the same limits and formats as the other four that write to
             * `users`. The username stays optional here: an admin created for
             * a person who only ever signs in by email does not need one.
             */
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'username' => $this->usernameRules(required: false),
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'phone' => $this->phoneRules(),
            'gender' => $this->genderRules(required: false),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'national_id' => $this->nationalIdRules(),
            'is_active' => ['sometimes', 'boolean'],

            /*
             * Restricted to the two administrative account types so this
             * screen can never mint a teacher or student. Whether the actor
             * may actually pick `owner` is decided server-side by
             * AdminGuardService, not by this rule.
             */
            'account_role' => ['required', 'string', Rule::in([
                UserRole::Owner->value,
                UserRole::Admin->value,
            ])],

            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'distinct', Rule::exists('roles', 'name')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('auth::admins.fields.name'),
            'email' => __('auth::admins.fields.email'),
            'username' => __('auth::admins.fields.username'),
            'password' => __('auth::admins.fields.password'),
            'phone' => __('auth::admins.fields.phone'),
            'account_role' => __('auth::admins.fields.account_role'),
            'roles' => __('auth::admins.fields.roles'),
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
