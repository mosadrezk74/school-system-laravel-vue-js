<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Timetable\DTOs\UpdatePeriodDTO;
use Modules\Timetable\Exceptions\TimetableOperationException;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Repositories\PeriodRepositoryInterface;

class UpdatePeriodAction extends BaseAction
{
    public function __construct(
        protected PeriodRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  array{period: Period, dto: UpdatePeriodDTO}  $input
     */
    public function execute(mixed $input): Period
    {
        /** @var Period $period */
        $period = $input['period'];
        /** @var UpdatePeriodDTO $dto */
        $dto = $input['dto'];

        if ($this->repository->existsByPeriodNumber($dto->periodNumber, $period->id)) {
            throw TimetableOperationException::duplicatePeriodNumber();
        }

        $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

        $dto = new UpdatePeriodDTO(
            periodNumber: $dto->periodNumber,
            name: $this->translationService->fillMissing($dto->name, $sourceLocale),
            startTime: $dto->startTime,
            endTime: $dto->endTime,
            isBreak: $dto->isBreak,
            isActive: $dto->isActive,
        );

        return $this->repository->update($period, $dto, auth()->id());
    }
}
