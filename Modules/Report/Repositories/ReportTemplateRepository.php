<?php

namespace Modules\Report\Repositories;

use Modules\Report\DTOs\CreateReportTemplateDTO;
use Modules\Report\DTOs\UpdateReportTemplateDTO;
use Modules\Report\Models\ReportGeneration;
use Modules\Report\Models\ReportTemplate;

class ReportTemplateRepository implements ReportTemplateRepositoryInterface
{
    public function findById(int $id): ?ReportTemplate
    {
        return ReportTemplate::query()->find($id);
    }

    public function findByIdOrFail(int $id): ReportTemplate
    {
        return ReportTemplate::query()->findOrFail($id);
    }

    public function findByCode(string $code): ?ReportTemplate
    {
        return ReportTemplate::query()->where('code', $code)->first();
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return ReportTemplate::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateReportTemplateDTO $dto, ?int $actorId = null): ReportTemplate
    {
        return ReportTemplate::query()->create([
            ...$dto->toArray(),
            'created_by' => $actorId,
        ]);
    }

    public function update(ReportTemplate $template, UpdateReportTemplateDTO $dto): ReportTemplate
    {
        $template->update($dto->toArray());

        return $template->refresh();
    }

    public function delete(ReportTemplate $template): bool
    {
        return (bool) $template->delete();
    }

    public function restore(ReportTemplate $template): bool
    {
        return (bool) $template->restore();
    }

    public function hasGenerations(ReportTemplate $template): bool
    {
        return ReportGeneration::query()->where('report_template_id', $template->id)->exists();
    }
}
