<?php

namespace Modules\Report\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Report\DTOs\UpdateReportTemplateDTO;
use Modules\Report\Exceptions\ReportOperationException;
use Modules\Report\Models\ReportTemplate;
use Modules\Report\Repositories\ReportTemplateRepositoryInterface;

class UpdateReportTemplateAction extends BaseAction
{
    public function __construct(
        protected ReportTemplateRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{reportTemplate: ReportTemplate, dto: UpdateReportTemplateDTO}  $input
     */
    public function execute(mixed $input): ReportTemplate
    {
        /** @var ReportTemplate $template */
        $template = $input['reportTemplate'];

        /** @var UpdateReportTemplateDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($template, $dto): ReportTemplate {
            if ($dto->code && $this->repository->existsByCode($dto->code, $template->id)) {
                throw ReportOperationException::codeAlreadyExists();
            }

            return $this->repository->update($template, $dto);
        });
    }
}
