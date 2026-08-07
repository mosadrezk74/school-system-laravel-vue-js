<?php

namespace Modules\Report\DTOs;

use Modules\Report\Http\Requests\UpdateReportTemplateRequest;

final readonly class UpdateReportTemplateDTO
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, mixed>|null  $queryData
     * @param  array<int, mixed>|null  $columns
     * @param  array<int, mixed>|null  $filters
     */
    public function __construct(
        public array $name,
        public ?string $code,
        public ?string $category,
        public ?array $queryData,
        public ?array $columns,
        public ?array $filters,
        public bool $isActive,
    ) {}

    public static function fromRequest(UpdateReportTemplateRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            code: $request->validated('code') ?: null,
            category: $request->validated('category') ?: null,
            queryData: $request->validated('query_data'),
            columns: $request->validated('columns'),
            filters: $request->validated('filters'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'category' => $this->category,
            'query_data' => $this->queryData,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'is_active' => $this->isActive,
        ];
    }
}
