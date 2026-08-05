<?php

namespace Modules\Classroom\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateClassroomRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:classrooms,code,' . $this->route('classroom') . ',id'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'floor' => ['nullable', 'integer', 'min:-5', 'max:50'],
            'building' => ['nullable', 'string', 'max:50'],
            'room_type' => ['required', 'string', 'in:classroom,lab,library,auditorium,gym,music_room,art_room,computer_lab,science_lab'],
            'has_projector' => ['sometimes', 'boolean'],
            'has_smartboard' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('classroom::classroom.fields.name'),
            'code' => __('classroom::classroom.fields.code'),
            'branch_id' => __('classroom::classroom.fields.branch_id'),
        ];
    }
}
