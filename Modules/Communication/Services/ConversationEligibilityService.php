<?php

namespace Modules\Communication\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Enums\UserRole;
use Modules\Communication\Exceptions\ConversationOperationException;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\ClassSubject;

/**
 * Who a given person is allowed to open a conversation with.
 *
 * The frontend never decides this. It asks for a directory, and every id it
 * later submits is re-checked here — so a picker that has been tampered with
 * buys nothing.
 *
 * Ownership questions ("which students does this teacher teach?") are not
 * re-derived here; they are asked of PortalScopeService, which is already the
 * authority for that and is what keeps the academic-year scoping consistent
 * with the rest of the application. This class only translates those answers
 * into the set of *user* ids that may be messaged.
 *
 * The permitted directions are:
 *
 *   staff     ↔ anyone
 *   teacher   ↔ their own students, those students' guardians, other staff
 *   student   ↔ teachers who teach their class, staff
 *   guardian  ↔ teachers who teach their children's classes, staff
 *
 * Student ↔ guardian is deliberately absent: families do not need the school's
 * message log to talk to each other, and routing it through here would put
 * private family conversation inside a system staff can be compelled to
 * disclose.
 */
class ConversationEligibilityService
{
    /** Roles that make up the school side of a conversation. */
    private const STAFF_ROLES = [UserRole::Owner, UserRole::Admin];

    public function __construct(
        private readonly PortalScopeService $scope,
        private readonly RecipientResolverService $recipients,
    ) {}

    /**
     * User ids the signed-in person may start a conversation with.
     *
     * Null means unrestricted, matching PortalScopeService's convention —
     * callers must not collapse null and [] together, because the first means
     * "everyone" and the second means "nobody".
     *
     * @return array<int, int>|null
     */
    public function eligibleUserIds(): ?array
    {
        $user = $this->scope->user();

        if (! $user instanceof User) {
            return [];
        }

        if ($this->scope->isStaff()) {
            return null;
        }

        $ids = match (true) {
            $this->scope->isTeacher() => $this->forTeacher(),
            $this->scope->isStudent() => $this->forStudent(),
            $this->scope->isGuardian() => $this->forGuardian(),
            default => [],
        };

        // Nobody may open a thread with themselves.
        return array_values(array_diff(array_unique($ids), [$user->id]));
    }

    /**
     * @param  array<int, int>  $userIds
     *
     * @throws ConversationOperationException
     */
    public function assertEligible(array $userIds): void
    {
        $eligible = $this->eligibleUserIds();

        if ($eligible === null) {
            $this->assertUsersExist($userIds);

            return;
        }

        if (array_diff($userIds, $eligible) !== []) {
            throw ConversationOperationException::ineligibleParticipants();
        }
    }

    /**
     * The picker's option list: eligible users, optionally narrowed by a
     * search term, capped so a large school cannot be enumerated in one call.
     *
     * @return Collection<int, User>
     */
    public function directory(?string $search = null, int $limit = 30): Collection
    {
        $eligible = $this->eligibleUserIds();
        $selfId = $this->scope->user()?->id;

        if ($eligible === [] || $selfId === null) {
            return collect();
        }

        return User::query()
            ->select(['id', 'name', 'role'])
            ->where('is_active', true)
            ->whereKeyNot($selfId)
            ->when($eligible !== null, fn (Builder $query) => $query->whereIn('id', $eligible))
            ->when(
                filled($search),
                fn (Builder $query) => $query->where('name', 'LIKE', '%'.trim((string) $search).'%'),
            )
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Students the teacher actually teaches this year, their guardians, and
     * the rest of the school's staff.
     *
     * @return array<int, int>
     */
    private function forTeacher(): array
    {
        $studentUserIds = $this->scope->teacherStudents()
            ->pluck('user_id')
            ->filter()
            ->values()
            ->all();

        $guardianUserIds = $studentUserIds === []
            ? []
            : $this->recipients->guardiansOfStudents($studentUserIds)->pluck('id')->all();

        return [
            ...$studentUserIds,
            ...$guardianUserIds,
            ...$this->staffUserIds(includeTeachers: true),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function forStudent(): array
    {
        $classId = $this->scope->currentStudentProfile()?->current_class_id;

        return [
            ...$this->teacherUserIdsForClasses($classId === null ? [] : [$classId]),
            ...$this->staffUserIds(),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function forGuardian(): array
    {
        $classIds = $this->scope->guardianChildren()
            ->pluck('current_class_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            ...$this->teacherUserIdsForClasses($classIds),
            ...$this->staffUserIds(),
        ];
    }

    /**
     * Teachers assigned to teach a subject in any of the given classes.
     *
     * Scoped to the current academic year through the assignment pivot, which
     * is where the year actually lives — `classes` no longer carries one. A
     * family therefore reaches this year's teachers, not every teacher the
     * child has ever had.
     *
     * @param  array<int, int>  $classIds
     * @return array<int, int>
     */
    private function teacherUserIdsForClasses(array $classIds): array
    {
        if ($classIds === []) {
            return [];
        }

        return ClassSubject::query()
            ->whereIn('class_id', $classIds)
            ->where('academic_year_id', AcademicContextService::yearId())
            ->whereNotNull('teacher_id')
            ->distinct()
            ->pluck('teacher_id')
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function staffUserIds(bool $includeTeachers = false): array
    {
        $roles = array_map(
            static fn (UserRole $role): string => $role->value,
            self::STAFF_ROLES,
        );

        if ($includeTeachers) {
            $roles[] = UserRole::Teacher->value;
        }

        return User::query()
            ->whereIn('role', $roles)
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }

    /**
     * Staff may message anyone, but "anyone" still has to be a real, active
     * account — otherwise a stale or invented id would create a conversation
     * with a participant row nobody can ever read.
     *
     * @param  array<int, int>  $userIds
     *
     * @throws ConversationOperationException
     */
    private function assertUsersExist(array $userIds): void
    {
        $found = User::query()
            ->whereIn('id', $userIds)
            ->where('is_active', true)
            ->count();

        if ($found !== count(array_unique($userIds))) {
            throw ConversationOperationException::ineligibleParticipants();
        }
    }
}
