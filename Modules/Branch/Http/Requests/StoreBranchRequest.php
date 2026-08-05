<?php

namespace Modules\Branch\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreBranchRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:branches,code'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_main' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('branch::branch.fields.name'),
            'code' => __('branch::branch.fields.code'),
            'address' => __('branch::branch.fields.address'),
            'city' => __('branch::branch.fields.city'),
            'phone' => __('branch::branch.fields.phone'),
            'manager_id' => __('branch::branch.fields.manager_id'),
            'is_main' => __('branch::branch.fields.is_main'),
            'is_active' => __('branch::branch.fields.is_active'),
        ];
    }
}