<?php

namespace Modules\Student\Actions;

use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\DTOs\CreateStudentDTO;
use Modules\Student\Exceptions\StudentOperationException;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Repositories\StudentRepositoryInterface;

class CreateStudentAction extends BaseAction
{
    public function __construct(
        protected StudentRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateStudentDTO  $input
     */
    public function execute(mixed $input): StudentProfile
    {
        /** @var CreateStudentDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): StudentProfile {
            if ($this->repository->existsByEmail($dto->email)) {
                throw StudentOperationException::emailAlreadyExists();
            }

            $admissionNumber = $dto->admissionNumber;

            if ($admissionNumber) {
                if ($this->repository->existsByAdmissionNumber($admissionNumber)) {
                    throw StudentOperationException::admissionNumberAlreadyExists();
                }
            } else {
                // Auto-generated scheme: <academic_year_id>-<sequence within that
                // year>, e.g. "3-0001". lockNextAdmissionSequence() takes a row
                // lock (held until this transaction commits/rolls back), so a
                // second concurrent create for the same year blocks here instead
                // of racing to read the same count and generating a duplicate —
                // the unique index on admission_number is the last-resort net,
                // this lock is what stops it from ever being needed.
                $sequence = $this->repository->lockNextAdmissionSequence($dto->academicYearId) + 1;

                $admissionNumber = sprintf('%d-%04d', $dto->academicYearId, $sequence);
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

            // User::password is cast 'hashed', so the plain value from the
            // DTO is hashed automatically on save — no manual bcrypt here.
            if ($admissionNumber !== $dto->admissionNumber) {
                $dto = new CreateStudentDTO(
                    name: $dto->name,
                    email: $dto->email,
                    username: $dto->username,
                    password: $dto->password,
                    gender: $dto->gender,
                    birthDate: $dto->birthDate,
                    nationalId: $dto->nationalId,
                    phone: $dto->phone,
                    isActive: $dto->isActive,
                    academicYearId: $dto->academicYearId,
                    currentClassId: $dto->currentClassId,
                    admissionNumber: $admissionNumber,
                    rollNumber: $dto->rollNumber,
                    admissionDate: $dto->admissionDate,
                    emergencyContactName: $dto->emergencyContactName,
                    emergencyContactPhone: $dto->emergencyContactPhone,
                    emergencyContactRelation: $dto->emergencyContactRelation,
                    address: $dto->address,
                    city: $dto->city,
                    fatherName: $dto->fatherName,
                    fatherPhone: $dto->fatherPhone,
                    motherName: $dto->motherName,
                    motherPhone: $dto->motherPhone,
                    guardianName: $dto->guardianName,
                    guardianPhone: $dto->guardianPhone,
                    guardianRelation: $dto->guardianRelation,
                    bloodGroup: $dto->bloodGroup,
                    allergies: $dto->allergies,
                    medicalConditions: $dto->medicalConditions,
                    previousSchool: $dto->previousSchool,
                    scholarshipPercentage: $dto->scholarshipPercentage,
                    isGraduated: $dto->isGraduated,
                    extraFields: $dto->extraFields,
                );
            }

            $student = $this->repository->create($dto, auth()->id());

            $student->user->assignRole('student');

            return $student->load('user');
        });
    }
}
