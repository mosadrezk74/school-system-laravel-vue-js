<?php

namespace Modules\Report\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Report\Models\ReportGeneration;

/**
 * @mixin ReportGeneration
 */
class ReportGenerationResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_template_id' => $this->report_template_id,
            'report_code' => $this->report_code,
            'filters_used' => $this->filters_used,
            'generated_by' => $this->generated_by,
            'format' => $this->format?->value,
            'format_label' => $this->format?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'file_url' => $this->file_url,
            'created_at' => $this->created_at?->toIso8601String(),

            'report_template' => $this->whenLoaded('reportTemplate', fn () => $this->reportTemplate ? [
                'id' => $this->reportTemplate->id,
                'name' => $this->reportTemplate->name,
                'code' => $this->reportTemplate->code,
            ] : null),

            'generated_by_user' => $this->whenLoaded('generatedBy', fn () => $this->generatedBy ? [
                'id' => $this->generatedBy->id,
                'name' => $this->generatedBy->name,
            ] : null),
        ];
    }
}
