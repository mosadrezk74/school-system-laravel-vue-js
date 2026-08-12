<?php

namespace Modules\SchoolClass\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class StoreClassSectionsRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],

            'sections' => ['required', 'array', 'min:1'],

            /*
             * `distinct` catches two rows named "3-A" in the same submission,
             * which the database would otherwise only discover on the second
             * insert — after the first had already been written inside the
             * transaction. The unique rule catches collisions with sections
             * already saved for this grade and year.
             */
            'sections.*.name' => [
                'required', 'string', 'max:100',
                'distinct:ignore_case',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query
                        ->where('grade_level_id', $this->input('grade_level_id'))
                        ->whereNull('deleted_at')),
            ],

            'sections.*.capacity' => ['required', 'integer', 'min:1'],
            'sections.*.classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'sections.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'grade_level_id' => __('school-class::class.fields.grade_level_id'),
            'sections' => __('school-class::class.fields.sections'),
            'sections.*.name' => __('school-class::class.fields.name'),
            'sections.*.capacity' => __('school-class::class.fields.capacity'),
            'sections.*.classroom_id' => __('school-class::class.fields.classroom_id'),
        ];
    }
}
