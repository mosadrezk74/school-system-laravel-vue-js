<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Interfaces\Repositories\SemesterRepositoryInterface;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseAction;

class RestoreSemesterAction extends BaseAction
{
    public function __construct(
        protected SemesterRepositoryInterface $repository,
    ) {}

    /**
     * @param  Semester  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Semester $semester */
        $semester = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($semester));
    }
}
