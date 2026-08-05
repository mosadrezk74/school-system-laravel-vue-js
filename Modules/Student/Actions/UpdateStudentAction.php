<?php

namespace Modules\Student\Actions;

use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\DTOs\UpdateStudentDTO;
use Modules\Student\Exceptions\StudentOperationException;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Repositories\StudentRepositoryInterface;

class UpdateStudentAction extends BaseAction
{
    public function __construct(
        protected StudentRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{student: StudentProfile, dto: UpdateStudentDTO}  $input
     */
    public function execute(mixed $input): StudentProfile
    {
        /** @var StudentProfile $student */
        $student = $input['student'];
        /** @var UpdateStudentDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($student, $dto): StudentProfile {
            if ($this->repository->existsByEmail($dto->email, $student->user_id)) {
                throw StudentOperationException::emailAlreadyExists();
            }

            if ($dto->admissionNumber && $this->repository->existsByAdmissionNumber($dto->admissionNumber, $student->id)) {
                throw StudentOperationException::admissionNumberAlreadyExists();
            }

            $academicYear = AcademicYear::find($dto->academicYearId);

            if (! $academicYear || ! $academicYear->is_active) {
                throw StudentOperationException::academicYearNotActive();
            }

            if ($dto->currentClassId) {
                $class = SchoolClass::find($dto->currentClassId);

                if (! $class || $class->academic_year_id !== $dto->academicYearId) {
                    throw StudentOperationException::classDoesNotBelongToYear();
                }
            }

            $student = $this->repository->update($student, $dto, auth()->id());

            return $student->load('user');
        });
    }
}
