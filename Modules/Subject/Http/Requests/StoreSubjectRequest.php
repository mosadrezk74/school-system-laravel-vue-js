<?php

namespace Modules\Subject\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreSubjectRequest extends BaseRequest
{
    /** `subjects.name` and `.description` are JSON, so these are domain limits. */
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

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('subject::subject.fields.name'),
            'description' => __('subject::subject.fields.description'),
        ];
    }
}
