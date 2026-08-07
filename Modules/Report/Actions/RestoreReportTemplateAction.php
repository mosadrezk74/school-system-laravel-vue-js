<?php

namespace Modules\Report\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Report\Models\ReportTemplate;
use Modules\Report\Repositories\ReportTemplateRepositoryInterface;

class RestoreReportTemplateAction extends BaseAction
{
    public function __construct(
        protected ReportTemplateRepositoryInterface $repository,
    ) {}

    /**
     * @param  ReportTemplate  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var ReportTemplate $template */
        $template = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($template));
    }
}
