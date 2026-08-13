<?php

namespace Modules\Setting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateSchoolTermRequest extends BaseRequest
{
    /** `school_terms.title`/`.content` are JSON. Terms and conditions run long, so the content cap is generous. */
    private const TITLE_MAX = 200;

    private const CONTENT_MAX = 50000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('title', max: self::TITLE_MAX),
            ...$this->translatableRules('content', max: self::CONTENT_MAX),
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
