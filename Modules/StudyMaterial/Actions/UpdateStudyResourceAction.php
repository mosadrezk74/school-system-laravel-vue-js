<?php

namespace Modules\StudyMaterial\Actions;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseAction;
use Modules\StudyMaterial\DTOs\UpdateStudyResourceDTO;
use Modules\StudyMaterial\Exceptions\StudyResourceOperationException;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Repositories\StudyResourceRepositoryInterface;

class UpdateStudyResourceAction extends BaseAction
{
    public function __construct(
        protected StudyResourceRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{studyResource: StudyResource, dto: UpdateStudyResourceDTO}  $input
     */
    public function execute(mixed $input): StudyResource
    {
        /** @var StudyResource $studyResource */
        $studyResource = $input['studyResource'];

        /** @var UpdateStudyResourceDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($studyResource, $dto): StudyResource {
            $hasExistingFile = $studyResource->file() !== null;

            if (! $dto->file && ! $dto->externalUrl && ! $hasExistingFile) {
                throw StudyResourceOperationException::fileOrUrlRequired();
            }

            $teacher = User::find($dto->teacherId);

            if (! $teacher || $teacher->role !== UserRole::Teacher) {
                throw StudyResourceOperationException::teacherIsNotATeacher();
            }

            $updated = $this->repository->update($studyResource, $dto, auth()->id());

            if ($dto->file) {
                $updated = $this->repository->attachFile($updated, $dto->file);
            }

            return $updated->load(['subject', 'gradeLevel', 'classroom', 'teacher', 'media']);
        });
    }
}
