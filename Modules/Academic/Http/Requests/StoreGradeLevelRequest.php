<?php

namespace Modules\Academic\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreGradeLevelRequest extends BaseRequest
{
    /** `grade_levels.name` and `.description` are JSON, so these are domain limits: a grade label is short, its blurb is a sentence or two. */
    private const NAME_MAX = 100;

    private const DESCRIPTION_MAX = 500;

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
            ...$this->translatableRules('name', max: self::NAME_MAX),
            ...$this->translatableRules('description', required: false, max: self::DESCRIPTION_MAX),

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
