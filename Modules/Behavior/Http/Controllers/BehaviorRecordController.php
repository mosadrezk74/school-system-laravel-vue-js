<?php

namespace Modules\Behavior\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Behavior\Actions\CreateBehaviorRecordAction;
use Modules\Behavior\Actions\DeleteBehaviorRecordAction;
use Modules\Behavior\Actions\UpdateBehaviorRecordAction;
use Modules\Behavior\DTOs\CreateBehaviorRecordDTO;
use Modules\Behavior\DTOs\UpdateBehaviorRecordDTO;
use Modules\Behavior\Enums\BehaviorRecordStatus;
use Modules\Behavior\Http\Requests\StoreBehaviorRecordRequest;
use Modules\Behavior\Http\Requests\UpdateBehaviorRecordRequest;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Behavior\Queries\BehaviorRecordQuery;
use Modules\Behavior\Transformers\BehaviorRecordResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Student\Models\StudentProfile;

class BehaviorRecordController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BehaviorRecord::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new BehaviorRecordQuery;

        $records = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Behavior::Records/Index', [
            'records' => BehaviorRecordResource::collection($records),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'behavior_category_id' => FilterOptions::fromModels(
                    BehaviorCategory::query()->where('is_active', true)->get(),
                ),
            ]),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', BehaviorRecord::class);

        return $this->render('Behavior::Records/Create', [
            'students' => $this->studentOptions(),
            'categories' => $this->activeCategoryOptions(),
            'studentId' => $request->filled('student_id') ? (int) $request->input('student_id') : null,

            /*
             * From the same enum the rule validates against. The form hard-coded
             * these two options with untranslated English labels, so an Arabic
             * user saw "Reported" and "Reviewed" regardless of locale.
             */
            'recordStatuses' => BehaviorRecordStatus::selectOptions(),
        ]);
    }

    public function store(StoreBehaviorRecordRequest $request, CreateBehaviorRecordAction $action): RedirectResponse
    {
        $this->authorize('create', BehaviorRecord::class);

        $action->execute([
            'dto' => CreateBehaviorRecordDTO::fromRequest($request),
            'reportedBy' => auth()->id(),
        ]);

        return $this->toRoute('records.index', [], __('behavior::behavior.created'));
    }

    public function show(BehaviorRecord $record): Response
    {
        $this->authorize('view', $record);

        $record->load(['student.user', 'behaviorCategory', 'reportedBy', 'reviewedBy']);

        return $this->render('Behavior::Records/Show', [
            'record' => new BehaviorRecordResource($record),
        ]);
    }

    public function edit(BehaviorRecord $record): Response
    {
        $this->authorize('update', $record);

        $record->load(['student.user', 'behaviorCategory', 'reportedBy', 'reviewedBy']);

        return $this->render('Behavior::Records/Edit', [
            'record' => new BehaviorRecordResource($record),
            'students' => $this->studentOptions(),
            'categories' => $this->activeCategoryOptions(),
            'recordStatuses' => BehaviorRecordStatus::selectOptions(),
        ]);
    }

    public function update(
        UpdateBehaviorRecordRequest $request,
        BehaviorRecord $record,
        UpdateBehaviorRecordAction $action,
    ): RedirectResponse {
        $this->authorize('update', $record);

        $action->execute([
            'record' => $record,
            'dto' => UpdateBehaviorRecordDTO::fromRequest($request),
        ]);

        return $this->toRoute('records.index', [], __('behavior::behavior.updated'));
    }

    public function destroy(BehaviorRecord $record, DeleteBehaviorRecordAction $action): RedirectResponse
    {
        $this->authorize('delete', $record);

        $action->execute($record);

        return $this->toRoute('records.index', [], __('behavior::behavior.deleted'));
    }

    /**
     * Kept light: id + user_id only, mapped down to id + name for the
     * picker — a full StudentProfile with all its relations would be
     * wasteful for a dropdown.
     *
     * @return array<int, array{id: int, name: ?string}>
     */
    protected function studentOptions(): array
    {
        return StudentProfile::query()
            ->with('user:id,name')
            ->get(['id', 'user_id'])
            ->map(fn (StudentProfile $student) => [
                'id' => $student->id,
                'name' => $student->user?->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, type: string, points: int}>
     */
    protected function activeCategoryOptions(): array
    {
        return BehaviorCategory::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'type', 'points'])
            ->map(fn (BehaviorCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'points' => $category->points,
            ])
            ->all();
    }
}
