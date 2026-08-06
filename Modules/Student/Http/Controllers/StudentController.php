<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicYearService;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Actions\ArchiveStudentAction;
use Modules\Student\Actions\CreateStudentAction;
use Modules\Student\Actions\RestoreStudentAction;
use Modules\Student\Actions\UpdateStudentAction;
use Modules\Student\DTOs\CreateStudentDTO;
use Modules\Student\DTOs\StudentFilterDTO;
use Modules\Student\DTOs\UpdateStudentDTO;
use Modules\Student\Http\Requests\StoreStudentRequest;
use Modules\Student\Http\Requests\UpdateStudentRequest;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Queries\StudentQuery;
use Modules\Student\Transformers\StudentResource;

class StudentController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudentProfile::class);

        $filters = StudentFilterDTO::fromRequest($request);

        $query = (new StudentQuery)
            ->searchUser($filters->search)
            ->academicYearId($filters->academicYearId)
            ->currentClassId($filters->currentClassId)
            ->gradeLevelId($filters->gradeLevelId)
            ->isActive($filters->isActive)
            ->gender($filters->gender)
            ->sort($filters->sortBy, $filters->sortOrder);

        $students = $query->paginate($filters->perPage);

        return $this->render('Student::Students/Index', [
            'students' => StudentResource::collection($students),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'current_class_id',
                'grade_level_id',
                'is_active',
                'gender',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StudentProfile::class);

        return $this->render('Student::Students/Create', [
            'classes' => SchoolClass::query()->where('is_active', true)->where('academic_year_id', AcademicYearService::id())
                ->with(['gradeLevel:id,name'])
                ->get(['id', 'name', 'section_code', 'grade_level_id', 'academic_year_id']),
        ]);
    }

    public function store(StoreStudentRequest $request, CreateStudentAction $action): RedirectResponse
    {
        $this->authorize('create', StudentProfile::class);

        $action->execute(CreateStudentDTO::fromRequest($request));

        return $this->toRoute('students.index', [], __('student::student.created'));
    }

    public function show(StudentProfile $student): Response
    {
        $this->authorize('view', $student);

        $student->load([
            'user', 'academicYear', 'currentClass.gradeLevel',
            'guardians.parent.user',
        ]);

        // Kept as a separate light query rather than a relation on
        // StudentProfile: Behavior is a separate module and this is the only
        // place the Student module needs to reach into it, so a direct query
        // here avoids coupling the two models together for one summary list.
        $recentBehaviorRecords = BehaviorRecord::where('student_id', $student->id)
            ->with('behaviorCategory')
            ->latest('date')
            ->limit(5)
            ->get();

        return $this->render('Student::Students/Show', [
            'student' => new StudentResource($student),
            'recentBehaviorRecords' => $recentBehaviorRecords,
        ]);
    }

    public function edit(StudentProfile $student): Response
    {
        $this->authorize('update', $student);

        $student->load('user');

        return $this->render('Student::Students/Edit', [
            'student' => new StudentResource($student),
            'classes' => SchoolClass::query()->where('is_active', true)->where('academic_year_id', AcademicYearService::id())
                ->with(['gradeLevel:id,name'])
                ->get(['id', 'name', 'section_code', 'grade_level_id', 'academic_year_id']),
        ]);
    }

    public function update(UpdateStudentRequest $request, StudentProfile $student, UpdateStudentAction $action): RedirectResponse
    {
        $this->authorize('update', $student);

        $action->execute(['student' => $student, 'dto' => UpdateStudentDTO::fromRequest($request)]);

        return $this->toRoute('students.index', [], __('student::student.updated'));
    }

    public function destroy(StudentProfile $student, ArchiveStudentAction $action): RedirectResponse
    {
        $this->authorize('delete', $student);

        $action->execute($student);

        return $this->toRoute('students.index', [], __('student::student.archived'));
    }

    public function restore(int $student, RestoreStudentAction $action): RedirectResponse
    {
        $student = StudentProfile::withTrashed()->findOrFail($student);

        $this->authorize('restore', $student);

        $action->execute($student);

        return $this->toRoute('students.index', [], __('student::student.restored'));
    }
}
