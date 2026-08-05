<?php

namespace Modules\GuardianProfile\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreGuardianRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'occupation' => ['nullable', 'string', 'max:255'],
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
}
