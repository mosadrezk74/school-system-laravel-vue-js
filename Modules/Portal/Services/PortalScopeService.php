<?php

namespace Modules\Portal\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Enums\UserRole;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Teacher\Models\TeacherProfile;

class PortalScopeService
{
    /** @var array<string, mixed> */
    private array $memo = [];

    public function user(): ?User
    {
        return auth()->user();
    }

    public function role(): ?UserRole
    {
        return $this->user()?->role;
    }

    public function isTeacher(): bool
    {
        return $this->role() === UserRole::Teacher;
    }

    public function isStudent(): bool
    {
        return $this->role() === UserRole::Student;
    }

    public function isGuardian(): bool
    {
        return $this->role() === UserRole::Guardian;
    }

    public function isStaff(): bool
    {
        return (bool) $this->role()?->isEmployee() && ! $this->isTeacher();
    }

    public function currentStudentProfile(): ?StudentProfile
    {
        return $this->remember('studentProfile', function (): ?StudentProfile {
            if (! $this->isStudent()) {
                return null;
            }

            return StudentProfile::query()
                ->with(['user', 'currentClass'])
                ->where('user_id', $this->user()?->id)
                ->first();
        });
    }

    public function currentTeacher(): ?TeacherProfile
    {
        return $this->remember('teacherProfile', function (): ?TeacherProfile {
            if (! $this->isTeacher()) {
                return null;
            }

            return TeacherProfile::query()
                ->with('user')
                ->where('user_id', $this->user()?->id)
                ->first();
        });
    }

    public function currentGuardian(): ?GuardianProfile
    {
        return $this->remember('guardianProfile', function (): ?GuardianProfile {
            if (! $this->isGuardian()) {
                return null;
            }

            return GuardianProfile::query()
                ->with('user')
                ->where('user_id', $this->user()?->id)
                ->first();
        });
    }

    /**
     * Teacher links are keyed on users.id, not teacher_profiles.id.
     */
    public function teacherUserId(): ?int
    {
        return $this->isTeacher() ? $this->user()?->id : null;
    }

    /**
     * @return Collection<int, StudentProfile>
     */
    public function guardianChildren(): Collection
    {
        return $this->remember('guardianChildren', function (): Collection {
            $guardian = $this->currentGuardian();

            if (! $guardian instanceof GuardianProfile) {
                return collect();
            }

            return StudentProfile::query()
                ->with(['user', 'currentClass'])
                ->whereIn('id', GuardianStudent::query()
                    ->where('parent_id', $guardian->id)
                    ->select('student_id'))
                ->get();
        });
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    public function teacherClasses(): Collection
    {
        return $this->remember('teacherClasses', function (): Collection {
            $teacherId = $this->teacherUserId();

            if ($teacherId === null) {
                return collect();
            }

            /*
             * Ownership now comes solely from the class_subjects pivot: the
             * classes table no longer carries class_teacher_id /
             * assistant_teacher_id, so a teacher reaches a class by being
             * assigned to teach a subject in it.
             */
            /*
             * Classes are no longer bound to an academic year, so the year
             * filter moved onto the assignment pivot, which is what actually
             * carries it. A teacher therefore still reaches only the classes
             * they teach *this* year, not every class they have ever taught.
             */
            return SchoolClass::query()
                ->with('gradeLevel')
                ->whereIn('id', ClassSubject::query()
                    ->where('teacher_id', $teacherId)
                    ->where('academic_year_id', AcademicContextService::yearId())
                    ->select('class_id'))
                ->get();
        });
    }

    /**
     * @return Collection<int, ClassSubject>
     */
    public function teacherSubjects(): Collection
    {
        return $this->remember('teacherSubjects', function (): Collection {
            $teacherId = $this->teacherUserId();

            if ($teacherId === null) {
                return collect();
            }

            return ClassSubject::query()
                ->with(['subject', 'class'])
                ->where('teacher_id', $teacherId)
                ->where('academic_year_id', AcademicContextService::yearId())
                ->get();
        });
    }

    /**
     * @return Collection<int, StudentProfile>
     */
    public function teacherStudents(): Collection
    {
        return $this->remember('teacherStudents', function (): Collection {
            $classIds = $this->teacherClasses()->pluck('id')->all();

            if ($classIds === []) {
                return collect();
            }

            return StudentProfile::query()
                ->with(['user', 'currentClass'])
                ->whereIn('current_class_id', $classIds)
                ->where('academic_year_id', AcademicContextService::yearId())
                ->get();
        });
    }

    /**
     * @return array<int, int>
     */
    public function teacherScheduleClassIds(): array
    {
        return $this->teacherClasses()->pluck('id')->all();
    }

    /**
     * @return array<int, int>
     */
    public function guardianChildIds(): array
    {
        return $this->guardianChildren()->pluck('id')->all();
    }

    /**
     * Grade levels reachable through the classes the user may see. Study
     * material is filed against a grade level rather than a class, so this
     * keeps that translation inside the ownership authority.
     *
     * @param  array<int, int>|null  $classIds
     * @return array<int, int>|null
     */
    public function gradeLevelIdsFor(?array $classIds = null): ?array
    {
        $classIds ??= $this->accessibleClassIds();

        if ($classIds === null) {
            return null;
        }

        if ($classIds === []) {
            return [];
        }

        return SchoolClass::query()
            ->whereIn('id', $classIds)
            ->pluck('grade_level_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function hasChildren(): bool
    {
        return $this->guardianChildIds() !== [];
    }

    /**
     * Resolves a requested child against the guardian's own children so a
     * portal controller never trusts an id that arrived from the frontend.
     */
    public function resolveChild(?int $studentId): ?StudentProfile
    {
        $children = $this->guardianChildren();

        if ($studentId === null) {
            return $children->first();
        }

        return $children->firstWhere('id', $studentId);
    }

    /**
     * Student profile ids the signed-in user may read.
     *
     * Null means unrestricted (staff); an empty array means nothing at all,
     * so callers must distinguish the two rather than treating both as "no
     * filter" and leaking every record.
     *
     * @return array<int, int>|null
     */
    public function accessibleStudentIds(): ?array
    {
        return $this->remember('accessibleStudentIds', function (): ?array {
            if ($this->isStaff()) {
                return null;
            }

            if ($this->isStudent()) {
                $id = $this->currentStudentProfile()?->id;

                return $id === null ? [] : [$id];
            }

            if ($this->isGuardian()) {
                return $this->guardianChildren()->pluck('id')->all();
            }

            if ($this->isTeacher()) {
                return $this->teacherStudents()->pluck('id')->all();
            }

            return [];
        });
    }

    /**
     * @return array<int, int>|null
     */
    public function accessibleClassIds(): ?array
    {
        return $this->remember('accessibleClassIds', function (): ?array {
            if ($this->isStaff()) {
                return null;
            }

            if ($this->isTeacher()) {
                return $this->teacherClasses()->pluck('id')->all();
            }

            if ($this->isStudent()) {
                $classId = $this->currentStudentProfile()?->current_class_id;

                return $classId === null ? [] : [$classId];
            }

            if ($this->isGuardian()) {
                return $this->guardianChildren()
                    ->pluck('current_class_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }

            return [];
        });
    }

    public function canAccessStudent(?int $studentId): bool
    {
        if ($studentId === null) {
            return false;
        }

        $accessible = $this->accessibleStudentIds();

        return $accessible === null || in_array($studentId, $accessible, true);
    }

    /**
     * Subject ids the signed-in user may act on. Null means unrestricted.
     *
     * @return array<int, int>|null
     */
    public function accessibleSubjectIds(): ?array
    {
        return $this->remember('accessibleSubjectIds', function (): ?array {
            if (! $this->isTeacher()) {
                return $this->isStaff() ? null : [];
            }

            return $this->teacherSubjects()
                ->pluck('subject_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
        });
    }

    public function canAccessSubject(?int $subjectId): bool
    {
        $accessible = $this->accessibleSubjectIds();

        if ($accessible === null) {
            return true;
        }

        return $subjectId !== null && in_array($subjectId, $accessible, true);
    }

    /**
     * A teacher may only touch a record sitting at the intersection of a class
     * and a subject they are actually assigned to teach.
     */
    public function canAccessClassSubject(?int $classId, ?int $subjectId): bool
    {
        if (! $this->isTeacher()) {
            return $this->canAccessClass($classId);
        }

        return $this->teacherSubjects()->contains(
            fn (ClassSubject $link): bool => $link->class_id === $classId
                && $link->subject_id === $subjectId
        );
    }

    public function canAccessClass(?int $classId): bool
    {
        if ($classId === null) {
            return false;
        }

        $accessible = $this->accessibleClassIds();

        return $accessible === null || in_array($classId, $accessible, true);
    }

    /**
     * A null grade level means the resource is school-wide rather than filed
     * against one grade, so it stays readable by anyone holding the permission.
     */
    public function canAccessGradeLevel(?int $gradeLevelId): bool
    {
        $accessible = $this->gradeLevelIdsFor();

        if ($accessible === null || $gradeLevelId === null) {
            return true;
        }

        return in_array($gradeLevelId, $accessible, true);
    }

    /**
     * Constrains a builder to the student ids the signed-in user may read.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    public function applyStudentScope(Builder $query, string $column = 'student_id'): Builder
    {
        $accessible = $this->accessibleStudentIds();

        if ($accessible === null) {
            return $query;
        }

        return $accessible === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn($column, $accessible);
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    public function applyClassScope(Builder $query, string $column = 'class_id'): Builder
    {
        $accessible = $this->accessibleClassIds();

        if ($accessible === null) {
            return $query;
        }

        return $accessible === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn($column, $accessible);
    }

    public function flush(): void
    {
        $this->memo = [];
    }

    private function remember(string $key, callable $callback): mixed
    {
        return $this->memo[$key] ??= $callback();
    }
}
