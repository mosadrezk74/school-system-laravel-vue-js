<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Validation\Validator;
use Modules\Core\Abstracts\BaseRequest;

class UpdateAcademicYearRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * `after:start_date` can't be used directly: a partial update may send
     * end_date alone, and the rule would then compare against nothing. Fall
     * back to the stored start_date when the payload omits it.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->has('end_date')) {
                return;
            }

            $year = $this->route('academic_year');
            $start = $this->date('start_date') ?? $year?->start_date;
            $end = $this->date('end_date');

            if ($start && $end && $end <= $start) {
                $validator->errors()->add(
                    'end_date',
                    __('validation.after', [
                        'attribute' => __('academic::academic_years.end_date'),
                        'date' => __('academic::academic_years.start_date'),
                    ]),
                );
            }
        });
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
