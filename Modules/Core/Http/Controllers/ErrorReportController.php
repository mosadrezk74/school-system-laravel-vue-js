<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Enums\ErrorReportStatus;
use Modules\Core\Http\Requests\UpdateErrorReportRequest;
use Modules\Core\Models\ErrorReport;
use Modules\Core\Queries\ErrorReportQuery;
use Modules\Core\Transformers\ErrorReportResource;

class ErrorReportController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ErrorReport::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new ErrorReportQuery;

        $reports = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return Inertia::render('Errors/Admin/Index', [
            'reports' => ErrorReportResource::collection($reports),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
            'statuses' => ErrorReportStatus::options(),
        ]);
    }

    public function show(Request $request, ErrorReport $errorReport): Response
    {
        $this->authorize('view', $errorReport);

        $errorReport->load(['user', 'resolver']);

        return Inertia::render('Errors/Admin/Show', [
            'report' => new ErrorReportResource($errorReport),

            /*
             * The trace is attached only for admins holding the dedicated
             * grant — it names internal files and classes, so viewing the
             * report itself is not enough.
             */
            'trace' => $request->user()->can('viewTrace', ErrorReport::class)
                ? $errorReport->trace
                : null,
        ]);
    }

    public function update(UpdateErrorReportRequest $request, ErrorReport $errorReport): RedirectResponse
    {
        $this->authorize('update', $errorReport);

        $status = ErrorReportStatus::from($request->validated('status'));
        $isResolved = $status === ErrorReportStatus::Resolved;

        $errorReport->update([
            'status' => $status,
            'resolved_at' => $isResolved ? now() : null,
            'resolved_by' => $isResolved ? $request->user()->id : null,
        ]);

        return $this->backWithSuccess(__('core::errors.admin.updated'));
    }
}
