<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;

class UpdateAdminRequest extends BaseRequest
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
        $adminId = $this->route('admin')?->getKey();

        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($adminId),
            'username' => $this->usernameRules($adminId, required: false),

            // Blank leaves the current password in place.
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],

            'phone' => $this->phoneRules($adminId),
            'gender' => $this->genderRules(required: false),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'national_id' => $this->nationalIdRules($adminId),
            'is_active' => ['sometimes', 'boolean'],

            'account_role' => ['sometimes', 'string', Rule::in([
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
