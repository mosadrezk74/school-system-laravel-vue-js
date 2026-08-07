<?php

namespace Modules\Report\DTOs;

use Modules\Report\Enums\ReportFormat;
use Modules\Report\Http\Requests\GenerateReportRequest;

final readonly class GenerateReportDTO
{
    public function __construct(
        public string $code,
        public ReportFilterDTO $filters,
        public string $format,
        public bool $persist,
    ) {}

    public static function fromRequest(GenerateReportRequest $request): self
    {
        return new self(
            code: $request->validated('code'),
            filters: ReportFilterDTO::fromArray($request->validated('filters', [])),
            format: $request->validated('format') ?: ReportFormat::Json->value,
            persist: (bool) $request->validated('persist', false),
        );
    }
}
