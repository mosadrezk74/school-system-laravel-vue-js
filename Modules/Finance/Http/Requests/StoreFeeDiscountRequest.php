<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\DiscountType;

class StoreFeeDiscountRequest extends BaseRequest
{
    /** `fee_discounts.name` is JSON, so this is a domain limit. */
    private const NAME_MAX = 100;

    /** `used_count` is an int; a discount usable more times than this is unbounded in practice. */
    private const USAGE_LIMIT_MAX = 100000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('name', max: self::NAME_MAX),
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'discount_type' => ['required', 'string', Rule::enum(DiscountType::class)],
            'discount_value' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'grade_level_id' => ['nullable', 'integer', 'exists:grade_levels,id', 'prohibits:classroom_id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            // `used_count` is an int, so the limit is bounded by the same column family.
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:'.self::USAGE_LIMIT_MAX],
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
            'code' => __('finance::finance.fields.code'),
            'discount_type' => __('finance::finance.fields.discount_type'),
            'discount_value' => __('finance::finance.fields.discount_value'),
            'max_amount' => __('finance::finance.fields.max_amount'),
            'grade_level_id' => __('finance::finance.fields.grade_level_id'),
            'classroom_id' => __('finance::finance.fields.classroom_id'),
            'start_date' => __('finance::finance.fields.start_date'),
            'end_date' => __('finance::finance.fields.end_date'),
            'usage_limit' => __('finance::finance.fields.usage_limit'),
            'is_active' => __('finance::finance.fields.is_active'),
        ];
    }
}
