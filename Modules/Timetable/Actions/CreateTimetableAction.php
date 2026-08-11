<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Timetable\DTOs\CreateTimetableDTO;
use Modules\Timetable\Exceptions\TimetableOperationException;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Repositories\TimetableRepositoryInterface;

class CreateTimetableAction extends BaseAction
{
    public function __construct(
        protected TimetableRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateTimetableDTO  $input
     */
    public function execute(mixed $input): Timetable
    {
        /** @var CreateTimetableDTO $dto */
        $dto = $input;

        if ($this->repository->existsForClassAndYear($dto->classId, $dto->academicYearId)) {
            throw TimetableOperationException::classAlreadyHasTimetableForYear();
        }

        /*
         * The unique index on (class_id, academic_year_id) ignores soft
         * deletes, so an archived timetable still occupies the slot. Revive it
         * instead of inserting a duplicate the database will reject.
         */
        $archived = $this->repository->findArchivedForClassAndYear($dto->classId, $dto->academicYearId);

        if ($archived instanceof Timetable) {
            return $this->repository->restoreWith($archived, $dto);
        }

        return $this->repository->create($dto, auth()->id());
    }
}
