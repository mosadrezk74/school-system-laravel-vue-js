<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateAcademicYearRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $year = $this->route('academic_year');
        $id = $year?->getKey();

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                // Ignore this row, or saving an unchanged code fails as a duplicate.
                Rule::unique('academic_years', 'code')->ignore($id),
            ],
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
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
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
            'code' => __('academic::academic_years.code'),
            'start_date' => __('academic::academic_years.start_date'),
            'end_date' => __('academic::academic_years.end_date'),
        ];
    }
}
