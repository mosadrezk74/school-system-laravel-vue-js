<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Report\Actions\CreateReportTemplateAction;
use Modules\Report\Actions\DeleteReportTemplateAction;
use Modules\Report\Actions\RestoreReportTemplateAction;
use Modules\Report\Actions\UpdateReportTemplateAction;
use Modules\Report\DTOs\CreateReportTemplateDTO;
use Modules\Report\DTOs\ReportTemplateFilterDTO;
use Modules\Report\DTOs\UpdateReportTemplateDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Http\Requests\StoreReportTemplateRequest;
use Modules\Report\Http\Requests\UpdateReportTemplateRequest;
use Modules\Report\Models\ReportTemplate;
use Modules\Report\Queries\ReportTemplateQuery;
use Modules\Report\Services\ReportRegistry;
use Modules\Report\Transformers\ReportTemplateResource;

class ReportTemplateController extends BaseInertiaController
{
    public function __construct(private readonly ReportRegistry $registry) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ReportTemplate::class);

        $filters = ReportTemplateFilterDTO::fromRequest($request);

        $templates = (new ReportTemplateQuery)
            ->searchTerm($filters->search)
            ->category($filters->category)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Report::Reports/Templates/Index', [
            'templates' => ReportTemplateResource::collection($templates),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'category', 'is_active']),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ReportTemplate::class);

        return $this->render('Report::Reports/Templates/Create', $this->lookups());
    }

    public function store(StoreReportTemplateRequest $request, CreateReportTemplateAction $action): RedirectResponse
    {
        $this->authorize('create', ReportTemplate::class);

        $action->execute(CreateReportTemplateDTO::fromRequest($request));

        return $this->toRoute('report-templates.index', [], __('report::report.template_created'));
    }

    public function edit(ReportTemplate $reportTemplate): Response
    {
        $this->authorize('update', $reportTemplate);

        $reportTemplate->load('createdBy');

        return $this->render('Report::Reports/Templates/Edit', [
            'template' => new ReportTemplateResource($reportTemplate),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateReportTemplateRequest $request, ReportTemplate $reportTemplate, UpdateReportTemplateAction $action): RedirectResponse
    {
        $this->authorize('update', $reportTemplate);

        $action->execute(['reportTemplate' => $reportTemplate, 'dto' => UpdateReportTemplateDTO::fromRequest($request)]);

        return $this->toRoute('report-templates.index', [], __('report::report.template_updated'));
    }

    public function destroy(ReportTemplate $reportTemplate, DeleteReportTemplateAction $action): RedirectResponse
    {
        $this->authorize('delete', $reportTemplate);

        $action->execute($reportTemplate);

        return $this->toRoute('report-templates.index', [], __('report::report.template_deleted'));
    }

    public function restore(int $reportTemplate, RestoreReportTemplateAction $action): RedirectResponse
    {
        $reportTemplate = ReportTemplate::withTrashed()->findOrFail($reportTemplate);

        $this->authorize('restore', $reportTemplate);

        $action->execute($reportTemplate);

        return $this->toRoute('report-templates.index', [], __('report::report.template_restored'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'categories' => ReportCategory::options(),
            'providers' => $this->registry->toMetadata(),
        ];
    }
}
