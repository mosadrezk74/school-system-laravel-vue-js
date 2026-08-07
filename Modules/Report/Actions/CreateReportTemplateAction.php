<?php

namespace Modules\Report\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Report\DTOs\CreateReportTemplateDTO;
use Modules\Report\Exceptions\ReportOperationException;
use Modules\Report\Models\ReportTemplate;
use Modules\Report\Repositories\ReportTemplateRepositoryInterface;

class CreateReportTemplateAction extends BaseAction
{
    public function __construct(
        protected ReportTemplateRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateReportTemplateDTO  $input
     */
    public function execute(mixed $input): ReportTemplate
    {
        /** @var CreateReportTemplateDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): ReportTemplate {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw ReportOperationException::codeAlreadyExists();
            }

            return $this->repository->create($dto, auth()->id());
        });
    }
}
