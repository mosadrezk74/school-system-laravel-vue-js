<?php

namespace Modules\Report\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Report\DTOs\GenerateReportDTO;
use Modules\Report\Enums\ReportGenerationStatus;
use Modules\Report\Models\ReportGeneration;

class ReportGenerationRepository implements ReportGenerationRepositoryInterface
{
    public function findById(int $id): ?ReportGeneration
    {
        return ReportGeneration::query()->find($id);
    }

    public function findByIdOrFail(int $id): ReportGeneration
    {
        return ReportGeneration::query()->findOrFail($id);
    }

    public function start(GenerateReportDTO $dto, ?int $templateId, ?int $actorId = null): ReportGeneration
    {
        return ReportGeneration::query()->create([
            'report_template_id' => $templateId,
            'report_code' => $dto->code,
            'filters_used' => $dto->filters->toArray(),
            'generated_by' => $actorId,
            'format' => $dto->format,
            'status' => ReportGenerationStatus::Pending->value,
        ]);
    }

    public function markCompleted(ReportGeneration $generation, ?string $fileUrl = null): ReportGeneration
    {
        $generation->update([
            'status' => ReportGenerationStatus::Completed->value,
            'file_url' => $fileUrl,
        ]);

        return $generation->refresh();
    }

    public function markFailed(ReportGeneration $generation): ReportGeneration
    {
        $generation->update(['status' => ReportGenerationStatus::Failed->value]);

        return $generation->refresh();
    }

    public function paginateRecent(int $perPage): LengthAwarePaginator
    {
        return ReportGeneration::query()
            ->with(['reportTemplate', 'generatedBy'])
            ->latest('created_at')
            ->paginate($perPage);
    }
}
