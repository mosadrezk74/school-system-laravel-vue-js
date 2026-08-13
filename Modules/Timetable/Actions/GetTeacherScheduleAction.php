<?php

namespace Modules\Timetable\Actions;

use Illuminate\Support\Collection;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseAction;
use Modules\Portal\Services\PortalScopeService;
use Modules\Timetable\Models\TimetableEntry;
use Modules\Timetable\Repositories\TimetableEntryRepositoryInterface;

class GetTeacherScheduleAction extends BaseAction
{
    public function __construct(
        protected TimetableEntryRepositoryInterface $repository,
        protected PortalScopeService $scope,
    ) {}

    /**
     * @param  array{dayOfWeek?: int|null}  $input
     * @return Collection<int, TimetableEntry>
     */
    public function execute(mixed $input = []): Collection
    {
        $teacherId = $this->scope->teacherUserId();

        if ($teacherId === null) {
            return collect();
        }

        return $this->repository->forTeacher(
            $teacherId,
            AcademicContextService::yearId() ?? 0,
            $input['dayOfWeek'] ?? null,
        );
    }
}
