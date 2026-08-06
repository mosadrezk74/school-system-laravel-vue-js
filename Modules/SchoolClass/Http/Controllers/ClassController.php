<?php

namespace Modules\SchoolClass\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\GradeLevel;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\SchoolClass\Actions\ArchiveClassAction;
use Modules\SchoolClass\Actions\CreateClassAction;
use Modules\SchoolClass\Actions\RestoreClassAction;
use Modules\SchoolClass\Actions\UpdateClassAction;
use Modules\SchoolClass\DTOs\ClassFilterDTO;
use Modules\SchoolClass\DTOs\CreateClassDTO;
use Modules\SchoolClass\DTOs\UpdateClassDTO;
use Modules\SchoolClass\Http\Requests\StoreClassRequest;
use Modules\SchoolClass\Http\Requests\UpdateClassRequest;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Queries\ClassQuery;
use Modules\SchoolClass\Transformers\ClassResource;
use Modules\Student\Models\StudentProfile;
use Modules\Teacher\Models\TeacherProfile;

class ClassController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SchoolClass::class);

        $filters = ClassFilterDTO::fromRequest($request);

        $query = (new ClassQuery)
            ->search($filters->search ?? '')
            ->academicYearId($filters->academicYearId)
            ->gradeLevelId($filters->gradeLevelId)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder);

        $classes = $query->paginate($filters->perPage);

        return $this->render('SchoolClass::Classes/Index', [
            'classes' => ClassResource::collection($classes),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'grade_level_id',
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SchoolClass::class);

        return $this->render('SchoolClass::Classes/Create', $this->lookups());
    }

    public function store(StoreClassRequest $request, CreateClassAction $action): RedirectResponse
    {
        $this->authorize('create', SchoolClass::class);

        $action->execute(CreateClassDTO::fromRequest($request));

        return $this->toRoute('classes.index', [], __('school-class::class.created'));
    }

    public function show(SchoolClass $class): Response
    {
        $this->authorize('view', $class);

        $class->load(['gradeLevel', 'academicYear', 'classroom', 'classTeacher', 'assistantTeacher']);

        $roster = StudentProfile::query()
            ->where('current_class_id', $class->id)
            ->with('user')
            ->get();

        return $this->render('SchoolClass::Classes/Show', [
            'class' => new ClassResource($class),
            'roster' => $roster->map(fn (StudentProfile $student) => [
                'id' => $student->id,
                'name' => $student->user->name,
                'admission_number' => $student->admission_number,
            ])->values(),
        ]);
    }

    public function edit(SchoolClass $class): Response
    {
        $this->authorize('update', $class);

        return $this->render('SchoolClass::Classes/Edit', [
            'class' => new ClassResource($class),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateClassRequest $request, SchoolClass $class, UpdateClassAction $action): RedirectResponse
    {
        $this->authorize('update', $class);

        $action->execute(['class' => $class, 'dto' => UpdateClassDTO::fromRequest($request)]);

        return $this->toRoute('classes.index', [], __('school-class::class.updated'));
    }

    public function destroy(SchoolClass $class, ArchiveClassAction $action): RedirectResponse
    {
        $this->authorize('delete', $class);

        $action->execute($class);

        return $this->toRoute('classes.index', [], __('school-class::class.archived'));
    }

    public function restore(int $class, RestoreClassAction $action): RedirectResponse
    {
        $class = SchoolClass::withTrashed()->findOrFail($class);

        $this->authorize('restore', $class);

        $action->execute($class);

        return $this->toRoute('classes.index', [], __('school-class::class.restored'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function lookups(): array
    {
        return [
            'gradeLevels' => GradeLevel::query()->where('is_active', true)->get()
                ->map(fn (GradeLevel $gradeLevel) => ['id' => $gradeLevel->id, 'name' => $gradeLevel->name])
                ->values(),
            'classrooms' => Classroom::query()->where('is_active', true)->get(['id', 'name']),
            'teachers' => TeacherProfile::query()
                ->where('is_active', true)
                ->with('user:id,name')
                ->get()
                ->map(fn (TeacherProfile $teacher) => [
                    'id' => $teacher->user_id,
                    'name' => $teacher->user->name,
                ])
                ->values(),
        ];
    }
}
