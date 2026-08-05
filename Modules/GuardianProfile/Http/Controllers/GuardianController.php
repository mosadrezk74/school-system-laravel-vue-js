<?php

namespace Modules\GuardianProfile\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\GuardianProfile\Actions\ArchiveGuardianAction;
use Modules\GuardianProfile\Actions\CreateGuardianAction;
use Modules\GuardianProfile\Actions\RestoreGuardianAction;
use Modules\GuardianProfile\Actions\UpdateGuardianAction;
use Modules\GuardianProfile\DTOs\CreateGuardianDTO;
use Modules\GuardianProfile\DTOs\GuardianFilterDTO;
use Modules\GuardianProfile\DTOs\UpdateGuardianDTO;
use Modules\GuardianProfile\Http\Requests\StoreGuardianRequest;
use Modules\GuardianProfile\Http\Requests\UpdateGuardianRequest;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\GuardianProfile\Queries\GuardianQuery;
use Modules\GuardianProfile\Transformers\GuardianResource;
use Modules\Student\Models\StudentProfile;

class GuardianController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', GuardianProfile::class);

        $filters = GuardianFilterDTO::fromRequest($request);

        $query = (new GuardianQuery)
            ->searchUser($filters->search)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder);

        $guardians = $query->paginate($filters->perPage);

        return $this->render('GuardianProfile::Guardians/Index', [
            'guardians' => GuardianResource::collection($guardians),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', GuardianProfile::class);

        return $this->render('GuardianProfile::Guardians/Create');
    }

    public function store(StoreGuardianRequest $request, CreateGuardianAction $action): RedirectResponse
    {
        $this->authorize('create', GuardianProfile::class);

        $action->execute(CreateGuardianDTO::fromRequest($request));

        return $this->toRoute('guardians.index', [], __('guardian::guardian.created'));
    }

    public function show(GuardianProfile $guardian): Response
    {
        $this->authorize('view', $guardian);

        $guardian->load(['user', 'students.student.user']);

        return $this->render('GuardianProfile::Guardians/Show', [
            'guardian' => new GuardianResource($guardian),
            'students' => StudentProfile::query()
                ->with('user:id,name')
                ->get(['id', 'user_id', 'admission_number']),
        ]);
    }

    public function edit(GuardianProfile $guardian): Response
    {
        $this->authorize('update', $guardian);

        $guardian->load('user');

        return $this->render('GuardianProfile::Guardians/Edit', [
            'guardian' => new GuardianResource($guardian),
        ]);
    }

    public function update(UpdateGuardianRequest $request, GuardianProfile $guardian, UpdateGuardianAction $action): RedirectResponse
    {
        $this->authorize('update', $guardian);

        $action->execute(['guardian' => $guardian, 'dto' => UpdateGuardianDTO::fromRequest($request)]);

        return $this->toRoute('guardians.index', [], __('guardian::guardian.updated'));
    }

    public function destroy(GuardianProfile $guardian, ArchiveGuardianAction $action): RedirectResponse
    {
        $this->authorize('delete', $guardian);

        $action->execute($guardian);

        return $this->toRoute('guardians.index', [], __('guardian::guardian.archived'));
    }

    public function restore(int $guardian, RestoreGuardianAction $action): RedirectResponse
    {
        $guardian = GuardianProfile::withTrashed()->findOrFail($guardian);

        $this->authorize('restore', $guardian);

        $action->execute($guardian);

        return $this->toRoute('guardians.index', [], __('guardian::guardian.restored'));
    }

    /**
     * Links a student to a guardian. Single-row, single-table write with no
     * cross-cutting business rules beyond the unique (parent_id, student_id)
     * constraint, so it is handled inline rather than via an Action.
     */
    public function linkStudent(Request $request, GuardianProfile $guardian): RedirectResponse
    {
        $this->authorize('update', $guardian);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'relationship' => ['required', 'string', 'max:50'],
            'is_primary_contact' => ['sometimes', 'boolean'],
            'can_pickup' => ['sometimes', 'boolean'],
            'receives_notifications' => ['sometimes', 'boolean'],
        ]);

        try {
            $guardian->students()->create([
                'student_id' => $data['student_id'],
                'relationship' => $data['relationship'],
                'is_primary_contact' => $data['is_primary_contact'] ?? true,
                'can_pickup' => $data['can_pickup'] ?? true,
                'receives_notifications' => $data['receives_notifications'] ?? true,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->backWithError(__('guardian::guardian.student_already_linked'));
            }

            throw $e;
        }

        return $this->backWithSuccess(__('guardian::guardian.student_linked'));
    }

    public function unlinkStudent(GuardianProfile $guardian, int $student): RedirectResponse
    {
        $this->authorize('update', $guardian);

        GuardianStudent::query()
            ->where('parent_id', $guardian->id)
            ->where('student_id', $student)
            ->delete();

        return $this->backWithSuccess(__('guardian::guardian.student_unlinked'));
    }
}
