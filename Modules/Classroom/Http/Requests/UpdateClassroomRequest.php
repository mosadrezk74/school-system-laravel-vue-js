<?php

namespace Modules\Classroom\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Classroom\Enums\RoomType;
use Modules\Core\Abstracts\BaseRequest;

class UpdateClassroomRequest extends BaseRequest
{
    /** `classrooms.name` is JSON; a room label such as "Science Lab 2" is short. */
    private const NAME_MAX = 100;

    public function rules(): array
    {
        return [
            /*
             * The form submits one plain string; its language is detected and
             * the remaining active locales are filled in by BaseRequest once
             * validation passes, so `name` reaches the DTO already keyed by
             * locale. On update the typed value is merged over the row's
             * stored translations, preserving untouched locales.
             */
            ...$this->translatableRules('name', max: self::NAME_MAX),
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'floor' => ['nullable', 'integer', 'min:-5', 'max:50'],
            'building' => ['nullable', 'string', 'max:50'],
            'room_type' => ['required', 'string', Rule::enum(RoomType::class)],
            'has_projector' => ['sometimes', 'boolean'],
            'has_smartboard' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('classroom::classroom.fields.name'),
        ];
    }
}
