<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateSemesterRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $semester = $this->route('semester');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            /*
             * The form submits one plain string; its language is detected and
             * the remaining active locales are filled in by BaseRequest once
             * validation passes, so `name` reaches the DTO already keyed by
             * locale. On update the typed value is merged over the row's
             * stored translations, preserving untouched locales.
             */
            ...$this->translatableRules('name'),

            /*
             * Same unique pair as the store request, with this row excluded so
             * saving an unchanged sequence is not reported as a duplicate.
             */
            'sequence' => [
                'required', 'integer', 'min:1', 'max:127',
                Rule::unique('semesters', 'sequence')
                    ->where(fn ($query) => $query->where('academic_year_id', $this->input('academic_year_id')))
                    ->ignore($semester?->getKey()),
            ],

            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'exam_start_date' => ['nullable', 'date'],
            'exam_end_date' => ['nullable', 'date', 'after_or_equal:exam_start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'academic_year_id' => __('academic::semesters.fields.academic_year_id'),
            'name' => __('academic::semesters.fields.name'),
            'sequence' => __('academic::semesters.fields.sequence'),
            'start_date' => __('academic::semesters.fields.start_date'),
            'end_date' => __('academic::semesters.fields.end_date'),
            'exam_start_date' => __('academic::semesters.fields.exam_start_date'),
            'exam_end_date' => __('academic::semesters.fields.exam_end_date'),
        ];
    }
}
