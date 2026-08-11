<?php

namespace Modules\Assignment\Support;

use Illuminate\Contracts\Validation\Validator;
use Modules\SchoolClass\Models\ClassSubject;

/**
 * Enforces that a submitted class/subject/teacher triple is a real teaching
 * assignment rather than three independently valid ids.
 *
 * The form narrows its dropdowns to the same data, but that is convenience
 * only: a crafted request pairing class A with subject B and teacher C has to
 * be rejected here, where the class_subjects row is the authority.
 */
trait ValidatesClassSubjectTeacher
{
    protected function validateClassSubjectTeacher(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $classId = $this->input('class_id');
        $subjectId = $this->input('subject_id');
        $teacherId = $this->input('teacher_id');

        if ($classId === null || $subjectId === null) {
            return;
        }

        $assignment = ClassSubject::query()
            ->where('class_id', (int) $classId)
            ->where('subject_id', (int) $subjectId)
            ->first();

        if ($assignment === null) {
            $validator->errors()->add(
                'subject_id',
                __('assignment::assignment.validation.subject_not_taught_by_class')
            );

            return;
        }

        if ($teacherId === null) {
            return;
        }

        if ($assignment->teacher_id === null || (int) $teacherId !== $assignment->teacher_id) {
            $validator->errors()->add(
                'teacher_id',
                __('assignment::assignment.validation.teacher_not_assigned_to_class_subject')
            );
        }
    }
}
