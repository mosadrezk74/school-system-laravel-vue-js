<?php

namespace Modules\Report\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Report\Models\ReportTemplate;

/**
 * @mixin ReportTemplate
 */
class ReportTemplateResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'category' => $this->category,
            'query_data' => $this->query_data,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),

            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null),
        ];
    }
}
