<?php

namespace Modules\StudyMaterial\Actions;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseAction;
use Modules\StudyMaterial\DTOs\CreateStudyResourceDTO;
use Modules\StudyMaterial\Exceptions\StudyResourceOperationException;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Repositories\StudyResourceRepositoryInterface;

class CreateStudyResourceAction extends BaseAction
{
    public function __construct(
        protected StudyResourceRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateStudyResourceDTO  $input
     */
    public function execute(mixed $input): StudyResource
    {
        /** @var CreateStudyResourceDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): StudyResource {
            if (! $dto->file && ! $dto->externalUrl) {
                throw StudyResourceOperationException::fileOrUrlRequired();
            }

            $teacher = User::find($dto->teacherId);

            if (! $teacher || $teacher->role !== UserRole::Teacher) {
                throw StudyResourceOperationException::teacherIsNotATeacher();
            }

            $studyResource = $this->repository->create($dto, auth()->id());

            if ($dto->file) {
                $studyResource = $this->repository->attachFile($studyResource, $dto->file);
            }

            return $studyResource->load(['subject', 'gradeLevel', 'classroom', 'teacher', 'media']);
        });
    }
}
