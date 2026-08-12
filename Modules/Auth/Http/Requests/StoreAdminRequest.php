<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseRequest;

class StoreAdminRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
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
            'roles.*' => ['string', Rule::exists('roles', 'name')],
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
}
