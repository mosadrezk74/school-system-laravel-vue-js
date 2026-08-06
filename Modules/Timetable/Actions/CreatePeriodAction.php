<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Timetable\DTOs\CreatePeriodDTO;
use Modules\Timetable\Exceptions\TimetableOperationException;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Repositories\PeriodRepositoryInterface;

class CreatePeriodAction extends BaseAction
{
    public function __construct(
        protected PeriodRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  CreatePeriodDTO  $input
     */
    public function execute(mixed $input): Period
    {
        /** @var CreatePeriodDTO $dto */
        $dto = $input;

        if ($this->repository->existsByPeriodNumber($dto->periodNumber)) {
            throw TimetableOperationException::duplicatePeriodNumber();
        }

        $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

        $dto = new CreatePeriodDTO(
            periodNumber: $dto->periodNumber,
            name: $this->translationService->fillMissing($dto->name, $sourceLocale),
            startTime: $dto->startTime,
            endTime: $dto->endTime,
            isBreak: $dto->isBreak,
            isActive: $dto->isActive,
        );

        return $this->repository->create($dto, auth()->id());
    }
}
