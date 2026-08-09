<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Response;
use Modules\Academic\Actions\ArchiveSemesterAction;
use Modules\Academic\Actions\CreateSemesterAction;
use Modules\Academic\Actions\RestoreSemesterAction;
use Modules\Academic\Actions\UpdateSemesterAction;
use Modules\Academic\DTOs\CreateSemesterDTO;
use Modules\Academic\DTOs\SemesterFilterDTO;
use Modules\Academic\DTOs\UpdateSemesterDTO;
use Modules\Academic\Http\Requests\StoreSemesterRequest;
use Modules\Academic\Http\Requests\UpdateSemesterRequest;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Queries\SemesterQuery;
use Modules\Academic\Transformers\SemesterResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

class SemesterController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Semester::class);

        $filters = SemesterFilterDTO::fromRequest($request);

        $semesters = (new SemesterQuery)
            ->withArchived()
            ->search($filters->search)
            ->academicYear($filters->academicYearId)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Academic::Semesters/Index', [
            'semesters' => SemesterResource::collection($semesters),
            'academicYears' => $this->academicYearOptions(),

            // Echoed back so filters survive a reload or a shared link.
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'academic_year_id',
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Semester::class);

        return $this->render('Academic::Semesters/Create', [
            'academicYears' => $this->academicYearOptions(),
        ]);
    }

    public function store(StoreSemesterRequest $request, CreateSemesterAction $action): RedirectResponse
    {
        $this->authorize('create', Semester::class);

        $action->execute(CreateSemesterDTO::fromRequest($request));

        return $this->toRoute('academic.semesters.index', [], __('academic::semesters.created'));
    }

    public function show(Semester $semester): Response
    {
        $this->authorize('view', $semester);

        $semester->load('academicYear:id,name');

        return $this->render('Academic::Semesters/Show', [
            'semester' => new SemesterResource($semester),
        ]);
    }

    public function edit(Semester $semester): Response
    {
        $this->authorize('update', $semester);

        $semester->load('academicYear:id,name');

        return $this->render('Academic::Semesters/Edit', [
            'semester' => new SemesterResource($semester),
            'academicYears' => $this->academicYearOptions(),
        ]);
    }

    public function update(
        UpdateSemesterRequest $request,
        Semester $semester,
        UpdateSemesterAction $action,
    ): RedirectResponse {
        $this->authorize('update', $semester);

        $action->execute([
            'semester' => $semester,
            'dto' => UpdateSemesterDTO::fromRequest($request),
        ]);

        return $this->toRoute('academic.semesters.index', [], __('academic::semesters.updated'));
    }

    public function destroy(Semester $semester, ArchiveSemesterAction $action): RedirectResponse
    {
        $this->authorize('delete', $semester);

        $action->execute($semester);

        return $this->toRoute('academic.semesters.index', [], __('academic::semesters.archived'));
    }

    public function restore(int $semester, RestoreSemesterAction $action): RedirectResponse
    {
        $semester = Semester::withTrashed()->findOrFail($semester);

        $this->authorize('restore', $semester);

        $action->execute($semester);

        return $this->toRoute('academic.semesters.index', [], __('academic::semesters.restored'));
    }

    /**
     * Only active years are offered: an archived or disabled year must not
     * gain new semesters. Two columns, so the select never loads full rows.
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    protected function academicYearOptions(): Collection
    {
        return AcademicYear::query()
            ->active()
            ->orderByDesc('start_date')
            ->get(['id', 'name'])
            ->map(fn (AcademicYear $year): array => [
                'id' => $year->id,
                'name' => $year->name,
            ])
            ->values();
    }
}
