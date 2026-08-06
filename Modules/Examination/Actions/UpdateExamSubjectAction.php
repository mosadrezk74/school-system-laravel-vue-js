<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\UpdateExamSubjectDTO;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Repositories\ExamSubjectRepositoryInterface;

class UpdateExamSubjectAction extends BaseAction
{
    public function __construct(
        protected ExamSubjectRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{examSubject: ExamSubject, dto: UpdateExamSubjectDTO}  $input
     */
    public function execute(mixed $input): ExamSubject
    {
        /** @var ExamSubject $examSubject */
        $examSubject = $input['examSubject'];
        /** @var UpdateExamSubjectDTO $dto */
        $dto = $input['dto'];

        return $this->repository->update($examSubject, $dto);
    }
}
