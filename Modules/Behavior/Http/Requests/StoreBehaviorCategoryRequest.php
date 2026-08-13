<?php

namespace Modules\Behavior\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Behavior\Enums\BehaviorType;
use Modules\Core\Abstracts\BaseRequest;

class StoreBehaviorCategoryRequest extends BaseRequest
{
    /** `behavior_categories.name` is JSON, so this is a domain limit. */
    private const NAME_MAX = 100;

    /** `points` is a smallint; a single incident worth more than this is a typo. */
    private const POINTS_MAX = 1000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('name', max: self::NAME_MAX),
            'type' => ['required', 'string', Rule::enum(BehaviorType::class)],

            /*
             * `behavior_categories.points` is a smallint, so the column alone
             * would accept ±32767 against a single incident. The sign carries
             * the meaning — merits add, violations subtract — so the range is
             * symmetric and the Action decides which way it applies.
             */
            'points' => ['required', 'integer', 'min:-'.self::POINTS_MAX, 'max:'.self::POINTS_MAX],
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
