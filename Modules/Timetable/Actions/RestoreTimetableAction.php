<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Repositories\TimetableRepositoryInterface;

class RestoreTimetableAction extends BaseAction
{
    public function __construct(
        protected TimetableRepositoryInterface $repository,
    ) {}

    /**
     * @param  Timetable  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Timetable $timetable */
        $timetable = $input;

        return $this->repository->restore($timetable);
    }
}
