<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Repositories\PeriodRepositoryInterface;

class RestorePeriodAction extends BaseAction
{
    public function __construct(
        protected PeriodRepositoryInterface $repository,
    ) {}

    /**
     * @param  Period  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Period $period */
        $period = $input;

        return $this->repository->restore($period);
    }
}
