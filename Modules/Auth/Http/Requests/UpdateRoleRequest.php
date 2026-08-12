<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateRoleRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->getKey();

        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_-]*$/',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],

            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('auth::roles.fields.name'),
            'permissions' => __('auth::roles.fields.permissions'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => __('auth::roles.validation.name_format'),
        ];
    }
}
