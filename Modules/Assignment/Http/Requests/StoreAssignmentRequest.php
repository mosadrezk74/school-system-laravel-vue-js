<?php

namespace Modules\Assignment\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Modules\Assignment\Support\ValidatesClassSubjectTeacher;
use Modules\Core\Abstracts\BaseRequest;

class StoreAssignmentRequest extends BaseRequest
{
    use ValidatesClassSubjectTeacher;

    /**
     * The academic year and semester are stamped from AcademicContextService in
     * the application layer, so they are deliberately absent here: a client
     * cannot file an assignment against a year or term of its choosing.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            ...$this->translatableRules('title'),
            ...$this->translatableRules('description', required: false),
            'max_score' => ['required', 'numeric', 'min:0'],
            'deadline' => ['required', 'date'],
            'assignment_type' => ['nullable', 'string', 'max:20'],
            'is_group_work' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(fn (Validator $validator) => $this->validateClassSubjectTeacher($validator));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => __('assignment::assignment.fields.class_id'),
            'subject_id' => __('assignment::assignment.fields.subject_id'),
            'teacher_id' => __('assignment::assignment.fields.teacher_id'),
            'title' => __('assignment::assignment.fields.title'),
            'max_score' => __('assignment::assignment.fields.max_score'),
            'deadline' => __('assignment::assignment.fields.deadline'),
        ];
    }
}
