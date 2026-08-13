<?php

namespace Modules\Assignment\Http\Requests;

use Illuminate\Validation\Validator;
use Modules\Assignment\Models\Assignment;
use Modules\Core\Abstracts\BaseRequest;

class GradeAssignmentSubmissionRequest extends BaseRequest
{
    /** `assignment_submissions.score` is decimal(6,2); `feedback` is text, so its cap is a domain choice. */
    private const SCORE_MAX = 9999.99;

    private const FEEDBACK_MAX = 2000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * `assignment_submissions.score` is decimal(6,2). The score also
             * cannot exceed the assignment's own max_score — checked in
             * withValidator, since that ceiling lives on the parent record
             * rather than in the payload.
             */
            'score' => ['required', 'numeric', 'min:0', 'max:'.self::SCORE_MAX],
            'grade' => ['nullable', 'string', 'max:10'],

            // `feedback` is a text column and previously had no cap at all.
            'feedback' => ['nullable', 'string', 'max:'.self::FEEDBACK_MAX],
        ];
    }

    /**
     * A score above the assignment's own `max_score` is not a valid mark: it
     * produces a percentage over 100 that then flows into the report card and
     * the class average. The ceiling lives on the parent record rather than in
     * the payload, so it cannot be expressed as an `lte:` rule.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Assignment|null $assignment */
            $assignment = $this->route('assignment');
            $score = $this->input('score');

            if (! $assignment || ! is_numeric($score)) {
                return;
            }

            if ((float) $score > (float) $assignment->max_score) {
                $validator->errors()->add('score', __('assignment::assignment.validation.score_above_max', [
                    'max' => $assignment->max_score,
                ]));
            }
        });
    }
}
