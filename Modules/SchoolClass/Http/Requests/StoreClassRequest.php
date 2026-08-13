<?php

namespace Modules\SchoolClass\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class StoreClassRequest extends BaseRequest
{
    /** Well beyond any real section, but low enough to catch a mistyped digit. */
    private const CAPACITY_MAX = 500;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],

            /*
             * A grade level holds many sections, so the name is what makes a
             * class unique inside its grade — not the grade level on
             * its own, which is what the previous rule checked and which
             * capped every grade at a single class.
             */
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query
                        ->where('grade_level_id', $this->input('grade_level_id'))
                        ->whereNull('deleted_at')),
            ],
            /*
             * `classes.capacity` is a smallint, so the column alone would
             * accept 32767 students in one section. 500 is already far beyond
             * any real class and keeps a mistyped extra digit out.
             */
            'capacity' => ['required', 'integer', 'min:1', 'max:'.self::CAPACITY_MAX],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'grade_level_id' => __('school-class::class.fields.grade_level_id'),
            'classroom_id' => __('school-class::class.fields.classroom_id'),
            'name' => __('school-class::class.fields.name'),
            'capacity' => __('school-class::class.fields.capacity'),
        ];
    }
}
