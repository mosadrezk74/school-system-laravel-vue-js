<?php

namespace Modules\Setting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreSchoolTermRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * The form submits one plain string per translatable field; its
             * language is detected and the remaining active locales are
             * filled in by BaseRequest once validation passes.
             */
            ...$this->translatableRules('title'),
            ...$this->translatableRules('content'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('setting::school_terms.fields.title'),
            'content' => __('setting::school_terms.fields.content'),
        ];
    }
}
