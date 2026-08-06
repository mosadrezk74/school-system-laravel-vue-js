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
            'active_weekdays' => ['required', 'array', 'min:1'],
            'active_weekdays.*' => ['integer', 'between:0,6'],
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
