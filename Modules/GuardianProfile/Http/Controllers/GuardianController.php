<?php

namespace Modules\GuardianProfile\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\GuardianProfile\Actions\ArchiveGuardianAction;
use Modules\GuardianProfile\Actions\CreateGuardianAction;
use Modules\GuardianProfile\Actions\LinkStudentToGuardianAction;
use Modules\GuardianProfile\Actions\RestoreGuardianAction;
use Modules\GuardianProfile\Actions\UnlinkStudentFromGuardianAction;
use Modules\GuardianProfile\Actions\UpdateGuardianAction;
use Modules\GuardianProfile\DTOs\CreateGuardianDTO;
use Modules\GuardianProfile\DTOs\LinkGuardianStudentDTO;
use Modules\GuardianProfile\DTOs\UpdateGuardianDTO;
use Modules\GuardianProfile\Enums\RelationshipType;
use Modules\GuardianProfile\Http\Requests\LinkStudentRequest;
use Modules\GuardianProfile\Http\Requests\StoreGuardianRequest;
use Modules\GuardianProfile\Http\Requests\UpdateGuardianRequest;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Queries\GuardianQuery;
use Modules\GuardianProfile\Transformers\GuardianResource;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Queries\StudentQuery;

class GuardianController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', GuardianProfile::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new GuardianQuery;

        $guardians = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('GuardianProfile::Guardians/Index', [
            'guardians' => GuardianResource::collection($guardians),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
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

    public function show(Request $request, GuardianProfile $guardian): Response
    {
        $this->authorize('view', $guardian);

        $guardian->load(['user', 'students.student.user']);

        return $this->render('GuardianProfile::Guardians/Show', [
            'guardian' => new GuardianResource($guardian),
            'students' => $this->studentOptions($request),

            /*
             * From the same enum the rule validates against. This was 50
             * characters of free text, so one parent could be filed as
             * "Father", "father" and "dad" across three children.
             */
            'relationships' => RelationshipType::selectOptions(),
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
    public function linkStudent(LinkStudentRequest $request, GuardianProfile $guardian, LinkStudentToGuardianAction $action): RedirectResponse
    {
        $this->authorize('update', $guardian);

        $action->execute([
            'guardian' => $guardian,
            'dto' => LinkGuardianStudentDTO::fromRequest($request),
        ]);

        return $this->backWithSuccess(__('guardian::guardian.student_linked'));
    }

    public function unlinkStudent(GuardianProfile $guardian, int $student, UnlinkStudentFromGuardianAction $action): RedirectResponse
    {
        $this->authorize('update', $guardian);

        $action->execute(['guardian' => $guardian, 'studentId' => $student]);

        return $this->backWithSuccess(__('guardian::guardian.student_unlinked'));
    }

    /**
     * The picker is searchable and paginated: loading every student would ship
     * the whole table into the page on a school of any real size.
     *
     * @return array<string, mixed>
     */
    private function studentOptions(Request $request): array
    {
        $students = (new StudentQuery)
            ->search($request->string('student_search')->toString() ?: null)
            ->where([
                'current_class_id' => $request->input('class_id'),
                'grade_level_id' => $request->input('grade_level_id'),
                'is_active' => true,
            ])
            ->paginate($request->integer('student_per_page', 15));

        return [
            'data' => $students->getCollection()
                ->map(fn (StudentProfile $student): array => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'admission_number' => $student->admission_number,
                    'class_name' => $student->currentClass?->name,
                ])
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'total' => $students->total(),
            ],
        ];
    }
}
