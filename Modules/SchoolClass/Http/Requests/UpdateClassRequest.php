<?php

namespace Modules\SchoolClass\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateClassRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $class = $this->route('class');

        return [
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],

            /*
             * Unique within its year and grade, ignoring the row being edited
             * so saving a class without renaming it does not collide with
             * itself.
             */
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query
                        ->where('grade_level_id', $this->input('grade_level_id'))
                        ->whereNull('deleted_at'))
                    ->ignore($class?->id),
            ],
            'capacity' => ['required', 'integer', 'min:1'],
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
