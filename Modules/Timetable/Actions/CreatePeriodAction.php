<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Timetable\DTOs\CreatePeriodDTO;
use Modules\Timetable\Exceptions\TimetableOperationException;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Repositories\PeriodRepositoryInterface;

class CreatePeriodAction extends BaseAction
{
    public function __construct(
        protected PeriodRepositoryInterface $repository,
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

        return $this->repository->create($dto, auth()->id());
    }
}
