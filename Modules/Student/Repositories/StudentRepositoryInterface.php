<?php

namespace Modules\Student\Repositories;

use Modules\Student\DTOs\CreateStudentDTO;
use Modules\Student\DTOs\UpdateStudentDTO;
use Modules\Student\Models\StudentProfile;

interface StudentRepositoryInterface
{
    public function findById(int $id): ?StudentProfile;

    public function findByIdOrFail(int $id): StudentProfile;

    public function findByAdmissionNumber(string $admissionNumber): ?StudentProfile;

    public function existsByEmail(string $email, ?int $excludeUserId = null): bool;

    public function existsByAdmissionNumber(string $admissionNumber, ?int $excludeId = null): bool;

    public function create(CreateStudentDTO $dto, ?int $actorId = null): StudentProfile;

    public function update(StudentProfile $student, UpdateStudentDTO $dto, ?int $actorId = null): StudentProfile;

    public function delete(StudentProfile $student): bool;

    public function restore(StudentProfile $student): bool;

    /**
     * Highest numeric sequence already used for this academic year's
     * auto-generated admission numbers, locked for update so a concurrent
     * caller in another transaction blocks until this one commits.
     */
    public function lockNextAdmissionSequence(int $academicYearId): int;
}
