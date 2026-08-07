<?php

namespace Modules\Finance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateFeeCategoryRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('name'),
            ...$this->translatableRules('description', false),
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('finance::finance.fields.name'),
            'description' => __('finance::finance.fields.description'),
            'code' => __('finance::finance.fields.code'),
            'is_active' => __('finance::finance.fields.is_active'),
        ];
    }
}
