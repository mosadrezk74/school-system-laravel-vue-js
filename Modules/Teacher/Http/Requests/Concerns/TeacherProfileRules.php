<?php

namespace Modules\Teacher\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Modules\Teacher\Enums\SalaryType;

/**
 * The `teacher_profiles` half of the teacher form. The store and update
 * requests differ only in the employee-id uniqueness ignore, so the rest lives
 * here rather than being maintained twice.
 */
trait TeacherProfileRules
{
    /** A teacher is an employee, so the plausible age range is an adult one. */
    protected const MIN_AGE = 18;

    protected const MAX_AGE = 100;

    /** `teacher_profiles` column widths. */
    protected const QUALIFICATION_MAX = 255;

    protected const SPECIALIZATION_MAX = 255;

    protected const POSITION_MAX = 100;

    /** `experience_years` is a tinyint, and 50 years is already a long career. */
    protected const EXPERIENCE_YEARS_MAX = 50;

    /** `salary_base` is decimal(10,2). */
    protected const SALARY_MAX = 99999999.99;

    /**
     * @return array<string, mixed>
     */
    protected function teacherProfileRules(): array
    {
        return [
            'qualification' => ['nullable', 'string', 'max:'.self::QUALIFICATION_MAX],
            'specialization' => ['nullable', 'string', 'max:'.self::SPECIALIZATION_MAX],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:'.self::EXPERIENCE_YEARS_MAX],

            /*
             * Someone cannot have joined before they were born, nor on a date
             * that has not happened yet. Both were previously accepted.
             */
            'joining_date' => ['nullable', 'date', 'before_or_equal:today', 'after:birth_date'],

            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'distinct', 'exists:subjects,id'],

            'position' => ['nullable', 'string', 'max:'.self::POSITION_MAX],
            'salary_type' => ['nullable', 'string', Rule::enum(SalaryType::class)],
            'salary_base' => ['nullable', 'numeric', 'min:0', 'max:'.self::SALARY_MAX],

            'primary_subjects' => ['nullable', 'array'],
            'primary_subjects.*' => ['integer', 'distinct', 'exists:subjects,id'],
        ];
    }

    /**
     * `employee_id` is a fixed seven-digit staff number, so `digits` is both
     * the format and the length.
     *
     * @return array<int, mixed>
     */
    protected function employeeIdRules(?int $ignoreProfileId = null): array
    {
        return [
            'nullable',
            'string',
            'digits:7',
            Rule::unique('teacher_profiles', 'employee_id')->ignore($ignoreProfileId),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function teacherMessages(): array
    {
        return [
            ...$this->accountFieldMessages(),
            'joining_date.after' => __('teacher::teacher.validation.joining_before_birth'),
            'joining_date.before_or_equal' => __('teacher::teacher.validation.joining_in_future'),
        ];
    }
}
