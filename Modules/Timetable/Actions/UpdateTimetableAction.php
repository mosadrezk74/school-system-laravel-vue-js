<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Timetable\DTOs\CreateTimetableDTO;
use Modules\Timetable\Exceptions\TimetableOperationException;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Repositories\TimetableRepositoryInterface;

class UpdateTimetableAction extends BaseAction
{
    public function __construct(
        protected TimetableRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{timetable: Timetable, dto: CreateTimetableDTO}  $input
     */
    public function execute(mixed $input): Timetable
    {
        /** @var Timetable $timetable */
        $timetable = $input['timetable'];
        /** @var CreateTimetableDTO $dto */
        $dto = $input['dto'];

        if ($this->repository->existsForClassAndYear($dto->classId, $dto->academicYearId, $timetable->id)) {
            throw TimetableOperationException::classAlreadyHasTimetableForYear();
        }

        return $this->repository->update($timetable, $dto, auth()->id());
    }
}
