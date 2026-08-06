<?php

namespace Modules\Subject\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Subject\Exceptions\SubjectOperationException;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;

class UpdateSubjectAction extends BaseAction
{
    public function __construct(
        protected SubjectRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    public function execute(mixed $input): Subject
    {
        $subject = $input['subject'];
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($subject, $dto): Subject {
            if ($dto->code && $this->repository->existsByCode($dto->code, $subject->id)) {
                throw SubjectOperationException::codeAlreadyExists();
            }

            $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

            $dto->name = $this->translationService->fillMissing($dto->name, $sourceLocale);

            if ($dto->description) {
                $dto->description = $this->translationService->fillMissing($dto->description, $sourceLocale);
            }

            return $this->repository->updateFromDTO($subject->id, $dto, auth()->id());
        });
    }
}
