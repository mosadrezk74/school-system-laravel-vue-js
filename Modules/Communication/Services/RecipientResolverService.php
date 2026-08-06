<?php

namespace Modules\Communication\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\Student\Models\StudentProfile;

class RecipientResolverService
{
    public const TYPE_ALL = 'all';

    public const TYPE_ROLE = 'role';


    public const TYPE_ACADEMIC_YEAR = 'academic_year';

    public const TYPE_CLASS = 'class';

    public const TYPE_SECTION = 'section';

    public const TYPE_USERS = 'users';

    /** @var array<int, string> */
    public const TYPES = [
        self::TYPE_ALL,
        self::TYPE_ROLE,
        self::TYPE_ACADEMIC_YEAR,
        self::TYPE_CLASS,
        self::TYPE_SECTION,
        self::TYPE_USERS,
    ];

    /**
     * @param  array<int, int|string>|null  $targetIds
     */
    public function resolve(string $targetType, ?array $targetIds): Builder
    {
        return match ($targetType) {
            self::TYPE_ALL => User::query(),
            self::TYPE_ROLE => $this->byRole($targetIds ?? []),
            self::TYPE_ACADEMIC_YEAR => $this->byAcademicYear($targetIds ?? []),
            self::TYPE_CLASS => $this->byClass($targetIds ?? []),
            self::TYPE_SECTION => $this->bySection($targetIds ?? []),
            self::TYPE_USERS => User::query()->whereIn('id', $targetIds ?? []),
            default => User::query()->whereRaw('1 = 0'),
        };
    }

    /**
     * @param  array<int, int|string>  $roles
     */
    private function byRole(array $roles): Builder
    {
        return User::query()->whereIn('role', $roles);
    }


    /**
     * @param  array<int, int>  $academicYearIds
     */
    private function byAcademicYear(array $academicYearIds): Builder
    {
        $studentUserIds = StudentProfile::query()
            ->whereIn('academic_year_id', $academicYearIds)
            ->pluck('user_id');

        return User::query()->whereIn('id', $studentUserIds);
    }

    /**
     * @param  array<int, int>  $classIds
     */
    private function byClass(array $classIds): Builder
    {
        $studentUserIds = StudentProfile::query()
            ->whereIn('current_class_id', $classIds)
            ->pluck('user_id');

        return User::query()->whereIn('id', $studentUserIds);
    }

    /**
     * @param  array<int, string>  $sectionCodes
     */
    private function bySection(array $sectionCodes): Builder
    {
        $studentUserIds = StudentProfile::query()
            ->whereHas('currentClass', fn (Builder $query) => $query->whereIn('section_code', $sectionCodes))
            ->pluck('user_id');

        return User::query()->whereIn('id', $studentUserIds);
    }

    /**
     * Guardians whose students match the given student user ids, used by
     * callers that also want to notify parents of a targeted student group.
     *
     * @param  array<int, int>  $studentUserIds
     */
    public function guardiansOfStudents(array $studentUserIds): Builder
    {
        $guardianUserIds = GuardianProfile::query()
            ->whereHas('students.student', fn (Builder $query) => $query->whereIn('user_id', $studentUserIds))
            ->pluck('user_id');

        return User::query()->whereIn('id', $guardianUserIds);
    }
}
