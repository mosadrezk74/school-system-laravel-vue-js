<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class StoreRoleRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * Lowercase kebab/snake only: Spatie resolves roles by exact
             * name and MySQL's collation is case-insensitive, so allowing
             * mixed case invites two rows that the app cannot tell apart.
             */
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_-]*$/',
                Rule::unique('roles', 'name'),
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
