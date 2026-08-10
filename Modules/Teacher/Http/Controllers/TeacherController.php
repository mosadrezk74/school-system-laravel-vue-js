<?php

namespace Modules\Teacher\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Actions\ArchiveTeacherAction;
use Modules\Teacher\Actions\CreateTeacherAction;
use Modules\Teacher\Actions\RestoreTeacherAction;
use Modules\Teacher\Actions\UpdateTeacherAction;
use Modules\Teacher\DTOs\CreateTeacherDTO;
use Modules\Teacher\DTOs\TeacherFilterDTO;
use Modules\Teacher\DTOs\UpdateTeacherDTO;
use Modules\Teacher\Http\Requests\StoreTeacherRequest;
use Modules\Teacher\Http\Requests\UpdateTeacherRequest;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Teacher\Queries\TeacherQuery;
use Modules\Teacher\Transformers\TeacherResource;

class TeacherController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TeacherProfile::class);

        $filters = TeacherFilterDTO::fromRequest($request);

        $query = (new TeacherQuery)
            ->searchUser($filters->search)
            ->isActive($filters->isActive)
            ->subjectId($filters->subjectId)
            ->gender($filters->gender)
            ->sort($filters->sortBy, $filters->sortOrder);

        $teachers = $query->paginate($filters->perPage);

        return Inertia::render('Teacher::Teachers/Index', [
            'teachers' => TeacherResource::collection($teachers),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'is_active',
                'subject_id',
                'gender',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', TeacherProfile::class);

        return Inertia::render('Teacher::Teachers/Create', [
            'subjects' => Subject::query()
                ->where('is_active', true)
                ->get(['id', 'name'])
                ->map(fn (Subject $subject): array => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                ])
                ->values(),
        ]);
    }

    public function store(StoreTeacherRequest $request, CreateTeacherAction $action): RedirectResponse
    {
        $this->authorize('create', TeacherProfile::class);

        $action->execute(CreateTeacherDTO::fromRequest($request));

        return $this->toRoute('teachers.index', [], __('teacher::teacher.created'));
    }

    public function show(TeacherProfile $teacher): Response
    {
        $this->authorize('view', $teacher);

        $teacher->load(['user', 'subjects']);

        return Inertia::render('Teacher::Teachers/Show', [
            'teacher' => new TeacherResource($teacher),
        ]);
    }

    public function edit(TeacherProfile $teacher): Response
    {
        $this->authorize('update', $teacher);

        $teacher->load(['user', 'subjects']);

        return Inertia::render('Teacher::Teachers/Edit', [
            'teacher' => new TeacherResource($teacher),
            'subjects' => Subject::query()
                ->where('is_active', true)
                ->get(['id', 'name'])
                ->map(fn (Subject $subject): array => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                ])
                ->values(),
        ]);
    }

    public function update(UpdateTeacherRequest $request, TeacherProfile $teacher, UpdateTeacherAction $action): RedirectResponse
    {
        $this->authorize('update', $teacher);

        $action->execute([
            'teacher' => $teacher,
            'dto' => UpdateTeacherDTO::fromRequest($request),
        ]);

        return $this->toRoute('teachers.index', [], __('teacher::teacher.updated'));
    }

    public function destroy(TeacherProfile $teacher, ArchiveTeacherAction $action): RedirectResponse
    {
        $this->authorize('delete', $teacher);

        $action->execute($teacher);

        return $this->toRoute('teachers.index', [], __('teacher::teacher.archived'));
    }

    public function restore(int $teacher, RestoreTeacherAction $action): RedirectResponse
    {
        $teacher = TeacherProfile::withTrashed()->findOrFail($teacher);

        $this->authorize('restore', $teacher);

        $action->execute($teacher);

        return $this->toRoute('teachers.index', [], __('teacher::teacher.restored'));
    }
}
