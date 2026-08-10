<?php

namespace Modules\Teacher\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Teacher\DTOs\CreateTeacherDTO;
use Modules\Teacher\DTOs\UpdateTeacherDTO;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;
use Modules\Teacher\Models\TeacherProfile;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function findById(int $id): ?TeacherProfile
    {
        return TeacherProfile::query()->find($id);
    }

    public function findOrFail(int $id): TeacherProfile
    {
        return TeacherProfile::query()->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        $query = TeacherProfile::query();

        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }

        return $query->get()->all();
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        $query = TeacherProfile::query()->with(['user', 'subjects']);

        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): TeacherProfile
    {
        return TeacherProfile::create($data);
    }

    public function update(int $id, array $data): ?TeacherProfile
    {
        $teacher = $this->findById($id);

        if (! $teacher) {
            return null;
        }

        $teacher->update($data);

        return $teacher->refresh();
    }

    public function delete(int $id): bool
    {
        $teacher = $this->findById($id);

        if (! $teacher) {
            return false;
        }

        return (bool) $teacher->delete();
    }

    public function findByField(string $field, mixed $value): ?TeacherProfile
    {
        return TeacherProfile::query()->where($field, $value)->first();
    }

    public function findManyByField(string $field, mixed $value): array
    {
        return TeacherProfile::query()->where($field, $value)->get()->all();
    }

    public function query(): Builder
    {
        return TeacherProfile::query();
    }

    public function beginTransaction(): void
    {
        \DB::beginTransaction();
    }

    public function commit(): void
    {
        \DB::commit();
    }

    public function rollback(): void
    {
        \DB::rollBack();
    }

    public function withTrashed(int $id): ?TeacherProfile
    {
        return TeacherProfile::withTrashed()->find($id);
    }

    public function restore(int $id): bool
    {
        $teacher = $this->withTrashed($id);

        if (! $teacher) {
            return false;
        }

        return (bool) $teacher->restore();
    }

    public function forceDelete(int $id): bool
    {
        $teacher = $this->withTrashed($id);

        if (! $teacher) {
            return false;
        }

        return (bool) $teacher->forceDelete();
    }

    public function findByEmployeeId(string $employeeId): ?TeacherProfile
    {
        return TeacherProfile::query()->where('employee_id', $employeeId)->first();
    }

    public function existsByEmployeeId(string $employeeId, ?int $excludeId = null): bool
    {
        return TeacherProfile::query()
            ->where('employee_id', $employeeId)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    /**
     * Builds an unused 7-digit employee id.
     *
     * Archived teachers keep their id — the unique index still covers
     * soft-deleted rows — so trashed records are included in the check.
     */
    public function generateEmployeeId(): string
    {
        do {
            $employeeId = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (
            TeacherProfile::withTrashed()
                ->where('employee_id', $employeeId)
                ->exists()
        );

        return $employeeId;
    }

    public function existsByEmail(string $email, ?int $excludeUserId = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($excludeUserId, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->exists();
    }

    /**
     * @param  int  $userId  Required: teacher_profiles.user_id is NOT NULL,
     *                       so it must be present on the insert itself rather than patched in
     *                       with a follow-up update after create().
     */
    public function createFromDTO(CreateTeacherDTO $dto, int $userId, ?int $actorId = null): TeacherProfile
    {
        $data = $dto->toArray();
        $data['user_id'] = $userId;
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        // Admins may leave the employee id blank; the school still needs one.
        if (blank($data['employee_id'])) {
            $data['employee_id'] = $this->generateEmployeeId();
        }

        return $this->create($data);
    }

    public function updateFromDTO(int $id, UpdateTeacherDTO $dto, ?int $actorId = null): TeacherProfile
    {
        $data = $dto->toArray();
        $data['updated_by'] = $actorId;

        // A teacher never loses their employee id: a blank field on edit keeps
        // the current one, and fills it in for rows created before it existed.
        if (blank($data['employee_id'])) {
            $existing = $this->findById($id)?->employee_id;

            $data['employee_id'] = $existing ?: $this->generateEmployeeId();
        }

        $teacher = $this->update($id, $data);

        if (! $teacher) {
            throw new \RuntimeException("Teacher with ID {$id} not found");
        }

        return $teacher;
    }

    public function getActiveTeachers(): iterable
    {
        return TeacherProfile::query()->where('is_active', true)->get();
    }

    public function getTeachersBySubject(int $subjectId): iterable
    {
        return TeacherProfile::query()
            ->whereHas('subjects', fn ($query) => $query->where('subjects.id', $subjectId))
            ->where('is_active', true)
            ->get();
    }
}
