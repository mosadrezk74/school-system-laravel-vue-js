<?php

namespace Modules\Timetable\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StorePeriodRequest extends BaseRequest
{
    /** `periods.period_number` is a tinyint. */
    private const PERIOD_NUMBER_MAX = 127;

    /** `periods.name` is JSON; a period label such as "Second Period" is short. */
    private const NAME_MAX = 50;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'period_number' => ['required', 'integer', 'min:1', 'max:'.self::PERIOD_NUMBER_MAX, 'unique:periods,period_number'],
            ...$this->translatableRules('name', max: self::NAME_MAX),
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'period_number' => __('timetable::timetable.fields.period_number'),
            'name' => __('timetable::timetable.fields.name'),
            'start_time' => __('timetable::timetable.fields.start_time'),
            'end_time' => __('timetable::timetable.fields.end_time'),
        ];
    }
}
