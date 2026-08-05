<?php

namespace Modules\Student\Repositories;

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Auth\Enums\UserRole;
use Modules\Student\DTOs\CreateStudentDTO;
use Modules\Student\DTOs\UpdateStudentDTO;
use Modules\Student\Models\StudentProfile;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class StudentRepository implements StudentRepositoryInterface
{
    public function findById(int $id): ?StudentProfile
    {
        return StudentProfile::query()->find($id);
    }

    public function findByIdOrFail(int $id): StudentProfile
    {
        return StudentProfile::query()->findOrFail($id);
    }

    public function findByAdmissionNumber(string $admissionNumber): ?StudentProfile
    {
        return StudentProfile::query()->where('admission_number', $admissionNumber)->first();
    }

    public function existsByEmail(string $email, ?int $excludeUserId = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($excludeUserId, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->exists();
    }

    public function existsByAdmissionNumber(string $admissionNumber, ?int $excludeId = null): bool
    {
        return StudentProfile::query()
            ->where('admission_number', $admissionNumber)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateStudentDTO $dto, ?int $actorId = null): StudentProfile
    {
        $user = User::create([
            ...$dto->toUserArray(),
            'role' => UserRole::Student->value,
        ]);

        return $user->studentProfile()->create([
            ...$dto->toProfileArray(),
            'academic_year_id' => $dto->academicYearId,
            'admission_number' => $dto->admissionNumber,
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(StudentProfile $student, UpdateStudentDTO $dto, ?int $actorId = null): StudentProfile
    {
        $student->user->update($dto->toUserArray());

        $student->update([
            ...$dto->toProfileArray(),
            'academic_year_id' => $dto->academicYearId,
            'admission_number' => $dto->admissionNumber,
            'updated_by' => $actorId,
        ]);

        return $student->refresh();
    }

    public function delete(StudentProfile $student): bool
    {
        return (bool) $student->delete();
    }

    public function restore(StudentProfile $student): bool
    {
        return (bool) $student->restore();
    }

    public function lockNextAdmissionSequence(int $academicYearId): int
    {
        // Locking the academic_years row (guaranteed to exist) rather than a
        // possibly-empty student_profiles subset: a row lock needs a row to
        // lock, and this one is always there, so it serialises every
        // "generate the next number for this year" caller against it.
        AcademicYear::query()->whereKey($academicYearId)->lockForUpdate()->firstOrFail();

        return StudentProfile::withTrashed()
            ->where('academic_year_id', $academicYearId)
            ->lockForUpdate()
            ->count();
    }
}
