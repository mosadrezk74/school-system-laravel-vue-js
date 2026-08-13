<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Models\Activity;
use Modules\Core\Queries\ActivityQuery;
use Modules\Core\Transformers\ActivityResource;

/**
 * Read-only access to the audit log.
 *
 * Every response reports the academic year and semester it was read under, so
 * a stored entry can be placed in the school calendar even though activity_log
 * carries no academic columns of its own.
 */
class ActivityController extends BaseInertiaController
{
    public function __construct(
        private readonly AcademicContextService $context,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Activity::class);

        $filters = ListQueryDTO::fromRequest($request);

        /*
         * `subject_type` + `subject_id` together are one record's history, not
         * two independent filters, so they stay a dedicated method — a bare
         * type would return every row of that model.
         */
        $query = (new ActivityQuery)->subject(
            $request->string('subject_type')->toString() ?: null,
            $request->filled('subject_id') ? (int) $request->input('subject_id') : null,
        );

        $activities = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('System/Activity/Index', [
            'activities' => ActivityResource::collection($activities),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
                ...$request->only(['subject_id']),
            ],
            'filterDefinitions' => $query->filterSchema(),

            /*
             * Academic context travels as response context, never as a filter:
             * `activity_log` has no academic-period column, so scoping entries
             * by year would label history with a period it was never filed
             * under.
             */
            'context' => $this->academicContext(),
        ]);
    }

    public function show(Activity $activity): Response
    {
        $this->authorize('view', $activity);

        $activity->load(['subject', 'causer']);

        return $this->render('System/Activity/Show', [
            'activity' => new ActivityResource($activity),
            'context' => $this->academicContext(),
        ]);
    }

    /**
     * The current year and semester, resolved from the single source of truth
     * and never from the query string.
     *
     * context() rather than requireContext(): the audit log has to stay
     * readable while a school is still being set up, so an unset year reports
     * null instead of failing the whole request.
     *
     * @return array{academic_year_id: int|null, semester_id: int|null}
     */
    private function academicContext(): array
    {
        return $this->context->context();
    }
}
