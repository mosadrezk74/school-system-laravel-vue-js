<?php

namespace Modules\Subject\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Subject\Exceptions\SubjectOperationException;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;

class CreateSubjectAction extends BaseAction
{
    public function __construct(
        protected SubjectRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    public function execute(mixed $input): Subject
    {
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Subject {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw SubjectOperationException::codeAlreadyExists();
            }

            $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

            $dto->name = $this->translationService->fillMissing($dto->name, $sourceLocale);

            if ($dto->description) {
                $dto->description = $this->translationService->fillMissing($dto->description, $sourceLocale);
            }

            return $this->repository->createFromDTO($dto, auth()->id());
        });
    }
}
