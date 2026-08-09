<?php

namespace Modules\SchoolClass\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Abstracts\BaseRequest;

class StoreClassRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grade_level_id' => [
                'required', 'integer', 'exists:grade_levels,id',
                Rule::unique('classes', 'grade_level_id')
                    ->where(fn ($query) => $query->where('academic_year_id', AcademicYearService::id())),
            ],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'name' => ['required', 'string', 'max:100'],
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
