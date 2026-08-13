<?php

namespace Modules\Finance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateFeeCategoryRequest extends BaseRequest
{
    /** `fee_categories.name`/`.description` are JSON, so these are domain limits. */
    private const NAME_MAX = 100;

    private const DESCRIPTION_MAX = 500;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('name', max: self::NAME_MAX),
            ...$this->translatableRules('description', false, max: self::DESCRIPTION_MAX),
            // Machine-readable, so it is kept to the same safe character set as the
            // other codes in the system rather than accepting any 50 characters.
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
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
