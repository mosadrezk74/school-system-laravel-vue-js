<?php

namespace Modules\Portal\Http\Controllers;

use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

class TeacherClassController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(): Response
    {
        $students = $this->scope->teacherStudents()->groupBy('current_class_id');

        return $this->render('Portal::Teacher/Classes', [
            'classes' => $this->scope->teacherClasses()
                ->map(fn (SchoolClass $class): array => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'section_code' => $class->section_code,
                    'grade_level' => $class->gradeLevel?->name,
                    'students_count' => $students->get($class->id)?->count() ?? 0,
                    'students' => ($students->get($class->id) ?? collect())
                        ->map(fn (StudentProfile $student): array => [
                            'id' => $student->id,
                            'name' => $student->user?->name,
                            'admission_number' => $student->admission_number,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values(),
        ]);
    }
}
