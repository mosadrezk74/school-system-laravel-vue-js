<?php

namespace Modules\Examination\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Examination\Enums\ExamType;

class UpdateExamRequest extends BaseRequest
{
    /** `exams.name` is JSON; an exam label is short. */
    private const NAME_MAX = 100;

    /** Column ceilings: passing_grade decimal(5,2), total_marks decimal(6,2), weightage decimal(4,2). */
    private const PASSING_GRADE_MAX = 999.99;

    private const TOTAL_MARKS_MAX = 9999.99;

    private const WEIGHTAGE_MAX = 99.99;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $exam = $this->route('exam');

        return [
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            ...$this->translatableRules('name', max: self::NAME_MAX),
            /*
             * A fixed list rather than 20 characters of free text. The exam
             * index filters and the report cards group by this column, so two
             * spellings of "midterm" split every one of those groupings.
             */
            'exam_type' => ['required', 'string', Rule::enum(ExamType::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            /*
             * Ceilings come from the columns: `passing_grade` is decimal(5,2),
             * `total_marks` decimal(6,2) and `weightage` decimal(4,2). Without
             * them a value that passed validation was silently truncated by
             * MySQL on the way in. The pass mark cannot exceed the paper's own
             * total, which is checked in withValidator because `lte` needs the
             * other field to be present.
             */
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:'.self::PASSING_GRADE_MAX],
            'total_marks' => ['nullable', 'numeric', 'min:0', 'max:'.self::TOTAL_MARKS_MAX],
            'weightage' => ['nullable', 'numeric', 'min:0', 'max:'.self::WEIGHTAGE_MAX],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'semester_id' => __('examination::exam.fields.semester_id'),
            'class_id' => __('examination::exam.fields.class_id'),
            'name' => __('examination::exam.fields.name'),
            'exam_type' => __('examination::exam.fields.exam_type'),
            'start_date' => __('examination::exam.fields.start_date'),
            'end_date' => __('examination::exam.fields.end_date'),
        ];
    }
}
