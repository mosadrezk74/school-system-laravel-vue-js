<?php

namespace Modules\Report\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Report\DTOs\GenerateReportDTO;
use Modules\Report\Models\ReportGeneration;

interface ReportGenerationRepositoryInterface
{
    public function findById(int $id): ?ReportGeneration;

    public function findByIdOrFail(int $id): ReportGeneration;

    public function start(GenerateReportDTO $dto, ?int $templateId, ?int $actorId = null): ReportGeneration;

    public function markCompleted(ReportGeneration $generation, ?string $fileUrl = null): ReportGeneration;

    public function markFailed(ReportGeneration $generation): ReportGeneration;

    public function paginateRecent(int $perPage): LengthAwarePaginator;
}
