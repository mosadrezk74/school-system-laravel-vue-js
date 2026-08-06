<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\CreateExamSubjectDTO;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Repositories\ExamSubjectRepositoryInterface;

class AddExamSubjectAction extends BaseAction
{
    public function __construct(
        protected ExamSubjectRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{exam: Exam, dto: CreateExamSubjectDTO}  $input
     */
    public function execute(mixed $input): ExamSubject
    {
        /** @var Exam $exam */
        $exam = $input['exam'];
        /** @var CreateExamSubjectDTO $dto */
        $dto = $input['dto'];

        return $this->repository->create($exam, $dto);
    }
}
