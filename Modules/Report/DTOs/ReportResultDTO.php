<?php

namespace Modules\Report\DTOs;

final readonly class ReportResultDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public array $rows,
        public array $columns,
        public array $summary = [],
        public array $metadata = [],
    ) {}

    /**
     * @return array{rows: array<int, array<string, mixed>>, columns: array<int, array<string, mixed>>, summary: array<string, mixed>, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'rows' => $this->rows,
            'columns' => $this->columns,
            'summary' => $this->summary,
            'metadata' => $this->metadata,
        ];
    }
}
