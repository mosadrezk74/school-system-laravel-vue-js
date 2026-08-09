<?php

namespace Modules\Academic\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreGradeLevelRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * The form submits one plain string per translatable field; its
             * language is detected and the remaining active locales are filled
             * in by BaseRequest once validation passes.
             */
            ...$this->translatableRules('name'),
            ...$this->translatableRules('description', required: false),

            'display_order' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('academic::grade_levels.fields.name'),
            'description' => __('academic::grade_levels.fields.description'),
            'display_order' => __('academic::grade_levels.fields.display_order'),
        ];
    }
}
