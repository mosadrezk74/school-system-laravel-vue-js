<?php

namespace Modules\Setting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateSchoolTermRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
