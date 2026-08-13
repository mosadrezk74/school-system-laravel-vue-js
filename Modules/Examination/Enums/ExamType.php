<?php

namespace Modules\Examination\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * `exams.exam_type` is filtered on in the exam list and grouped by in the
 * report cards, so it was never really free text — it only looked that way
 * because the rule was `string|max:20`. Two spellings of "midterm" split every
 * one of those groupings in half.
 *
 * The first four cases are the ones the form's own hard-coded dropdown already
 * offered, so no existing selection changes meaning; `practical` and `oral`
 * fill the obvious gaps.
 */
enum ExamType: string
{
    use BaseEnum;

    case Quiz = 'quiz';
    case UnitTest = 'unit_test';
    case Midterm = 'midterm';
    case Final = 'final';
    case Practical = 'practical';
    case Oral = 'oral';

    public function label(): string
    {
        return __("examination::exam.types.{$this->value}");
    }
}
