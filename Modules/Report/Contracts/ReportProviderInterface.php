<?php

namespace Modules\Report\Contracts;

use Modules\Report\DTOs\ReportFilterDTO;
use Modules\Report\DTOs\ReportResultDTO;

interface ReportProviderInterface
{
    public function code(): string;

    public function name(): string;

    public function category(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function filters(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function columns(): array;

    public function generate(ReportFilterDTO $filters): ReportResultDTO;
}
