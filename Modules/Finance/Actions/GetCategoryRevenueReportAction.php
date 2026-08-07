<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\ReportFilterDTO;
use Modules\Finance\Queries\FinanceReportQuery;

class GetCategoryRevenueReportAction extends BaseAction
{
    public function __construct(
        protected FinanceReportQuery $query,
    ) {}

    /**
     * @param  ReportFilterDTO  $input
     */
    public function execute(mixed $input): Collection
    {
        /** @var ReportFilterDTO $filters */
        $filters = $input;

        return $this->query->categoryRevenue($filters, AcademicYearService::id());
    }
}
