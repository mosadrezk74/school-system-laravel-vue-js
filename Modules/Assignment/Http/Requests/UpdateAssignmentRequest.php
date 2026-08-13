<?php

namespace Modules\Assignment\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Modules\Assignment\Enums\AssignmentType;
use Modules\Assignment\Support\ValidatesClassSubjectTeacher;
use Modules\Core\Abstracts\BaseRequest;

class UpdateAssignmentRequest extends BaseRequest
{
    /** `assignments.title`/`.description` are JSON, so these are domain limits. */
    private const TITLE_MAX = 150;

    private const DESCRIPTION_MAX = 2000;

    /** `assignments.max_score` is decimal(6,2). */
    private const MAX_SCORE_CEILING = 9999.99;

    use ValidatesClassSubjectTeacher;

    /**
     * An assignment keeps the academic year and semester it was created in, so
     * editing never restamps them — historical records stay where they belong.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            ...$this->translatableRules('title', max: self::TITLE_MAX),
            ...$this->translatableRules('description', required: false, max: self::DESCRIPTION_MAX),
            // `assignments.max_score` is decimal(6,2). A zero-mark assignment cannot
            // be graded meaningfully, so the floor is exclusive.
            'max_score' => ['required', 'numeric', 'gt:0', 'max:'.self::MAX_SCORE_CEILING],
            'deadline' => ['required', 'date'],
            // A fixed list rather than free text: the create form seeded this with an
            // empty string, so in practice it held whatever each teacher typed.
            'assignment_type' => ['nullable', 'string', Rule::enum(AssignmentType::class)],
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
