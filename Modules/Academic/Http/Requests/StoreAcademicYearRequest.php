<?php

namespace Modules\Academic\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreAcademicYearRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('academic::academic_years.name'),
            'start_date' => __('academic::academic_years.start_date'),
            'end_date' => __('academic::academic_years.end_date'),
        ];
    }
}
