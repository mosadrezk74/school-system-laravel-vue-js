<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ActivityFilterDTO;
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

        $filters = ActivityFilterDTO::fromRequest($request);

        $activities = (new ActivityQuery)
            ->search($filters->search)
            ->logName($filters->logName)
            ->event($filters->event)
            ->subject($filters->subjectType, $filters->subjectId)
            ->causer($filters->causerId)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('System/Activity/Index', [
            'activities' => ActivityResource::collection($activities),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'log_name',
                'event',
                'subject_type',
                'subject_id',
                'causer_id',
            ]),
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
