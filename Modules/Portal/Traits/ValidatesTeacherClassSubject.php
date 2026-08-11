<?php

namespace Modules\Portal\Traits;

use Illuminate\Validation\Validator;
use Modules\Portal\Services\PortalScopeService;

/**
 * A teacher may only file portal work against a class/subject pair they are
 * actually assigned to teach — the class_subjects assignment, not the
 * teacher_subject qualification.
 *
 * This runs as an after-validation hook rather than a rule so the failure is
 * reported against the field the form owns, and so it can express the
 * intersection of the two values instead of checking each in isolation.
 */
trait ValidatesTeacherClassSubject
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $classId = $this->input('school_class_id');
            $subjectId = $this->input('subject_id');

            if ($classId === null || $classId === '') {
                return;
            }

            $scope = app(PortalScopeService::class);

            if ($subjectId === null || $subjectId === '') {
                if (! $scope->canAccessClass((int) $classId)) {
                    $validator->errors()->add(
                        'school_class_id',
                        __('portal::portal.not_your_class'),
                    );
                }

                return;
            }

            if (! $scope->canAccessClassSubject((int) $classId, (int) $subjectId)) {
                $validator->errors()->add(
                    'subject_id',
                    __('portal::portal.not_your_class_subject'),
                );
            }
        });
    }
}
