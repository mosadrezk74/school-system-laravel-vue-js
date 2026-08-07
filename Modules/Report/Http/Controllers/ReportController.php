<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Report\Actions\GenerateReportAction;
use Modules\Report\DTOs\GenerateReportDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Http\Requests\GenerateReportRequest;
use Modules\Report\Models\ReportTemplate;
use Modules\Report\Services\ReportRegistry;

class ReportController extends BaseInertiaController
{
    public function __construct(private readonly ReportRegistry $registry) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ReportTemplate::class);

        return $this->render('Report::Reports/Index', [
            'reports' => $this->registry->toMetadata(
                $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
                $request->string('category')->toString() ?: null,
            ),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'category']),
            'categories' => ReportCategory::options(),
        ]);
    }

    public function generate(GenerateReportRequest $request, GenerateReportAction $action): JsonResponse
    {
        $this->authorize('generate', ReportTemplate::class);

        $result = $action->execute(GenerateReportDTO::fromRequest($request));

        return response()->json($result->toArray());
    }
}
