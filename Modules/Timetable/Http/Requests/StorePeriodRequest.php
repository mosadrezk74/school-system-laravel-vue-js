<?php

namespace Modules\Timetable\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StorePeriodRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'period_number' => ['required', 'integer', 'min:1', 'unique:periods,period_number'],
            ...$this->translatableRules('name'),
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
