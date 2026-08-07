<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Repositories\StudentFeeRepositoryInterface;

class RestoreStudentFeeAction extends BaseAction
{
    public function __construct(
        protected StudentFeeRepositoryInterface $repository,
    ) {}

    /**
     * @param  StudentFee  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var StudentFee $studentFee */
        $studentFee = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($studentFee));
    }
}
