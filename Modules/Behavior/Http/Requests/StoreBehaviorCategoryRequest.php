<?php

namespace Modules\Behavior\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreBehaviorCategoryRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:behavior_categories,code'],
            'type' => ['required', 'string', 'in:merit,violation'],
            'points' => ['required', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('behavior::behavior.fields.name'),
            'code' => __('behavior::behavior.fields.code'),
            'type' => __('behavior::behavior.fields.type'),
            'points' => __('behavior::behavior.fields.points'),
        ];
    }
}
