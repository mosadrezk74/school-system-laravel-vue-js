<?php

namespace Modules\Classroom\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Classroom\Actions\ArchiveClassroomAction;
use Modules\Classroom\Actions\CreateClassroomAction;
use Modules\Classroom\Actions\RestoreClassroomAction;
use Modules\Classroom\Actions\UpdateClassroomAction;
use Modules\Classroom\DTOs\ClassroomFilterDTO;
use Modules\Classroom\DTOs\CreateClassroomDTO;
use Modules\Classroom\DTOs\UpdateClassroomDTO;
use Modules\Classroom\Enums\RoomType;
use Modules\Classroom\Http\Requests\StoreClassroomRequest;
use Modules\Classroom\Http\Requests\UpdateClassroomRequest;
use Modules\Classroom\Models\Classroom;
use Modules\Classroom\Queries\ClassroomQuery;
use Modules\Classroom\Transformers\ClassroomResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

class ClassroomController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Classroom::class);

        $filters = ClassroomFilterDTO::fromRequest($request);

        $query = (new ClassroomQuery)
            ->search($filters->search)
            ->roomType($filters->roomType)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder);

        $classrooms = $query->paginate($filters->perPage);

        return Inertia::render('Classroom::Classrooms/Index', [
            'classrooms' => ClassroomResource::collection($classrooms),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'room_type',
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Classroom::class);

        return Inertia::render('Classroom::Classrooms/Create', [
            'roomTypes' => RoomType::selectOptions(),
        ]);
    }

    public function store(StoreClassroomRequest $request, CreateClassroomAction $action): RedirectResponse
    {
        $this->authorize('create', Classroom::class);

        $action->execute(CreateClassroomDTO::fromRequest($request));

        return $this->toRoute('classrooms.index', [], __('classroom::classroom.created'));
    }

    public function show(Classroom $classroom): Response
    {
        $this->authorize('view', $classroom);

        return Inertia::render('Classroom::Classrooms/Show', [
            'classroom' => new ClassroomResource($classroom),
        ]);
    }

    public function edit(Classroom $classroom): Response
    {
        $this->authorize('update', $classroom);

        return Inertia::render('Classroom::Classrooms/Edit', [
            'classroom' => new ClassroomResource($classroom),
            'roomTypes' => RoomType::selectOptions(),
        ]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom, UpdateClassroomAction $action): RedirectResponse
    {
        $this->authorize('update', $classroom);

        $action->execute([
            'classroom' => $classroom,
            'dto' => UpdateClassroomDTO::fromRequest($request),
        ]);

        return $this->toRoute('classrooms.index', [], __('classroom::classroom.updated'));
    }

    public function destroy(Classroom $classroom, ArchiveClassroomAction $action): RedirectResponse
    {
        $this->authorize('delete', $classroom);

        $action->execute($classroom);

        return $this->toRoute('classrooms.index', [], __('classroom::classroom.archived'));
    }

    public function restore(int $classroom, RestoreClassroomAction $action): RedirectResponse
    {
        $classroom = Classroom::withTrashed()->findOrFail($classroom);

        $this->authorize('restore', $classroom);

        $action->execute($classroom);

        return $this->toRoute('classrooms.index', [], __('classroom::classroom.restored'));
    }
}
