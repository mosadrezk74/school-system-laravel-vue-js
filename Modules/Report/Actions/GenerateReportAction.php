<?php

namespace Modules\Report\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Report\DTOs\GenerateReportDTO;
use Modules\Report\DTOs\ReportResultDTO;
use Modules\Report\Repositories\ReportGenerationRepositoryInterface;
use Modules\Report\Repositories\ReportTemplateRepositoryInterface;
use Modules\Report\Services\ReportRegistry;
use Throwable;

class GenerateReportAction extends BaseAction
{
    public function __construct(
        protected ReportRegistry $registry,
        protected ReportGenerationRepositoryInterface $generations,
        protected ReportTemplateRepositoryInterface $templates,
    ) {}

    /**
     * @param  GenerateReportDTO  $input
     */
    public function execute(mixed $input): ReportResultDTO
    {
        /** @var GenerateReportDTO $dto */
        $dto = $input;

        $provider = $this->registry->get($dto->code);

        if (! $dto->persist) {
            return $provider->generate($dto->filters);
        }

        $generation = $this->generations->start(
            $dto,
            $this->templates->findByCode($dto->code)?->id,
            auth()->id(),
        );

        try {
            $result = $provider->generate($dto->filters);
        } catch (Throwable $exception) {
            $this->generations->markFailed($generation);

            throw $exception;
        }

        $this->generations->markCompleted($generation);

        return $result;
    }
}
