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
            ...$this->translatableRules('name'),
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
            'type' => __('behavior::behavior.fields.type'),
            'points' => __('behavior::behavior.fields.points'),
        ];
    }
}
