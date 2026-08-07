<?php

namespace Modules\Report\Repositories;

use Modules\Report\DTOs\CreateReportTemplateDTO;
use Modules\Report\DTOs\UpdateReportTemplateDTO;
use Modules\Report\Models\ReportTemplate;

interface ReportTemplateRepositoryInterface
{
    public function findById(int $id): ?ReportTemplate;

    public function findByIdOrFail(int $id): ReportTemplate;

    public function findByCode(string $code): ?ReportTemplate;

    public function existsByCode(string $code, ?int $excludeId = null): bool;

    public function create(CreateReportTemplateDTO $dto, ?int $actorId = null): ReportTemplate;

    public function update(ReportTemplate $template, UpdateReportTemplateDTO $dto): ReportTemplate;

    public function delete(ReportTemplate $template): bool;

    public function restore(ReportTemplate $template): bool;

    public function hasGenerations(ReportTemplate $template): bool;
}
