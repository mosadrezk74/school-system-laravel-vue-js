<?php

namespace Modules\Timetable\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateWeekdaySettingsRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // There are seven weekdays and a day cannot be active twice, so the array
            // is bounded by the domain itself rather than left open.
            'active_weekdays' => ['required', 'array', 'min:1', 'max:7'],
            'active_weekdays.*' => ['integer', 'between:0,6', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'active_weekdays' => __('timetable::timetable.fields.active_weekdays'),
        ];
    }
}
