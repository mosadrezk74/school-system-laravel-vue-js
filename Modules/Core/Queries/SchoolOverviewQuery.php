<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Classroom\Models\Classroom;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Teacher\Models\TeacherProfile;

/**
 * Headcount figures for the admin dashboard.
 *
 * Every method is a single aggregate — no model hydration — because the
 * dashboard only ever renders the resulting scalar.
 */
class SchoolOverviewQuery
{
    /**
     * Students currently enrolled in the given academic year.
     *
     * A student counts when their profile is active, not yet graduated, and
     * their user account is neither disabled nor soft-deleted. Without the
     * user join a deactivated login would still inflate the roll.
     */
    public function activeStudents(?int $academicYearId): int
    {
        return $this->enrolledStudents($academicYearId)->count();
    }

    /**
     * Students admitted within the last $days, scoped to the same year as the
     * headline figure so the two numbers are directly comparable.
     */
    public function recentAdmissions(?int $academicYearId, int $days = 30): int
    {
        return $this->enrolledStudents($academicYearId)
            ->whereNotNull('admission_date')
            ->where('admission_date', '>=', now()->subDays($days)->toDateString())
            ->count();
    }

    /**
     * Enrolled students grouped by the gender recorded on their user account.
     *
     * Gender is nullable, so students without one are omitted rather than
     * bucketed into a guessed category.
     *
     * @return array<string, int>
     */
    public function studentsByGender(?int $academicYearId): array
    {
        return $this->enrolledStudents($academicYearId)
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->whereNotNull('users.gender')
            ->selectRaw('users.gender as gender, COUNT(*) as aggregate')
            ->groupBy('users.gender')
            ->pluck('aggregate', 'gender')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    /**
     * Enrolled students per class for the year, largest first.
     *
     * Uses a grouped join instead of withCount on every class so the cost is
     * one query regardless of how many classes exist.
     *
     * @return array<int, array{id: int, name: string, students: int, capacity: int|null}>
     */
    public function classCapacityUsage(?int $academicYearId, int $limit = 6): array
    {
        return SchoolClass::query()
            ->where('classes.is_active', true)
            ->withCount(['students as students_count' => fn (Builder $query) => $query
                ->where('student_profiles.is_active', true)
                ->where('student_profiles.is_graduated', false)
                ->whereNull('student_profiles.deleted_at')])
            ->orderByDesc('students_count')
            ->limit($limit)
            ->get(['classes.id', 'classes.name', 'classes.capacity'])
            ->map(fn (SchoolClass $class): array => [
                'id' => $class->id,
                'name' => $class->name,
                'students' => (int) $class->students_count,
                'capacity' => $class->capacity !== null ? (int) $class->capacity : null,
            ])
            ->all();
    }

    /**
     * Guardians with an active account.
     *
     * Guardian profiles carry no academic year — a family stays on file
     * across years — so this is a school-wide figure.
     */
    public function activeGuardians(): int
    {
        return GuardianProfile::query()
            ->where('parent_profiles.is_active', true)
            ->whereHas('user', fn (Builder $user) => $user->where('is_active', true))
            ->count();
    }

    /**
     * Bookable rooms. Physical rooms outlive any single academic year, so this
     * is deliberately not year-scoped.
     */
    public function activeClassrooms(): int
    {
        return Classroom::query()->where('is_active', true)->count();
    }

    /** Teachers on staff. Teacher profiles are not scoped to an academic year. */
    public function activeTeachers(): int
    {
        return TeacherProfile::query()
            ->where('teacher_profiles.is_active', true)
            ->whereHas('user', fn (Builder $user) => $user->where('is_active', true))
            ->count();
    }

    public function activeClasses(?int $academicYearId): int
    {
        // Classes are no longer bound to a year, so the count is school-wide.
        return SchoolClass::query()
            ->where('is_active', true)
            ->count();
    }

    /**
     * Active classes for the year that have no enrolled students — an
     * actionable gap, since a class with no roll is usually unfinished setup.
     */
    public function classesWithoutStudents(?int $academicYearId): int
    {
        return SchoolClass::query()
            ->where('is_active', true)
            ->whereDoesntHave('students', fn (Builder $query) => $query
                ->where('student_profiles.is_active', true)
                ->where('student_profiles.is_graduated', false))
            ->count();
    }

    /**
     * Enrolled students not yet assigned to a class.
     */
    public function studentsWithoutClass(?int $academicYearId): int
    {
        return $this->enrolledStudents($academicYearId)
            ->whereNull('current_class_id')
            ->count();
    }

    /**
     * @return Builder<StudentProfile>
     */
    private function enrolledStudents(?int $academicYearId): Builder
    {
        return StudentProfile::query()
            ->when($academicYearId, fn (Builder $query) => $query->where('student_profiles.academic_year_id', $academicYearId))
            ->where('student_profiles.is_active', true)
            ->where('student_profiles.is_graduated', false)
            ->whereHas('user', fn (Builder $user) => $user->where('is_active', true));
    }
}
