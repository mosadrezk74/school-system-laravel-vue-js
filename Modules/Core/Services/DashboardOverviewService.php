<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Route;
use Modules\Core\Queries\DashboardAttendanceQuery;
use Modules\Core\Queries\DashboardFinanceQuery;
use Modules\Core\Queries\SchoolOverviewQuery;

/**
 * Assembles the admin dashboard payload.
 *
 * Each section is built by its own method against its own query object, and a
 * section is included only when the module is enabled and the signed-in user
 * holds the matching permission — so finance figures never reach a user who
 * may not view them, rather than being hidden in the browser.
 *
 * A section that the user may not see is absent from the payload (null),
 * which the page renders differently from a section that is present but empty.
 */
class DashboardOverviewService
{
    public function __construct(
        private readonly SchoolOverviewQuery $school,
        private readonly DashboardAttendanceQuery $attendance,
        private readonly DashboardFinanceQuery $finance,
        private readonly ModuleRegistry $modules,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?int $academicYearId): array
    {
        $finance = $this->finance($academicYearId);

        return [
            'people' => $this->people($academicYearId),
            'attendance' => $this->attendance($academicYearId),
            'finance' => $finance,
            /*
             * The finance section is passed through so the attention panel can
             * reuse the overdue and pending figures already computed above
             * instead of running those aggregates a second time.
             */
            'attention' => $this->attention($academicYearId, $finance),
            'quickActions' => $this->quickActions(),
        ];
    }

    /**
     * Shortcuts the signed-in user is actually allowed to use.
     *
     * Authorising here rather than in the page means an unauthorised action is
     * never sent to the browser at all.
     *
     * @return array<int, array{key: string, url: string}>
     */
    private function quickActions(): array
    {
        $candidates = [
            ['key' => 'addStudent', 'module' => 'Student', 'permission' => 'student.create', 'route' => 'students.create'],
            ['key' => 'addTeacher', 'module' => 'Teacher', 'permission' => 'teacher.create', 'route' => 'teachers.create'],
            ['key' => 'createClass', 'module' => 'SchoolClass', 'permission' => 'school-class.create', 'route' => 'classes.create'],
            ['key' => 'createInvoice', 'module' => 'Finance', 'permission' => 'invoice.create', 'route' => 'invoices.create'],
        ];

        $actions = [];

        foreach ($candidates as $candidate) {
            if (! $this->allows($candidate['module'], $candidate['permission'])) {
                continue;
            }

            if (! Route::has($candidate['route'])) {
                continue;
            }

            $actions[] = [
                'key' => $candidate['key'],
                'url' => route($candidate['route']),
            ];
        }

        return $actions;
    }

    /**
     * Headcounts. Student and teacher figures are gated separately because a
     * user may hold one permission without the other.
     *
     * @return array<string, mixed>
     */
    private function people(?int $academicYearId): array
    {
        $canSeeStudents = $this->allows('Student', 'student.view');
        $canSeeClasses = $this->allows('SchoolClass', 'school-class.view');

        return [
            'students' => $canSeeStudents ? $this->school->activeStudents($academicYearId) : null,
            'newStudents' => $canSeeStudents ? $this->school->recentAdmissions($academicYearId) : null,
            'studentsByGender' => $canSeeStudents ? $this->school->studentsByGender($academicYearId) : null,
            'teachers' => $this->allows('Teacher', 'teacher.view')
                ? $this->school->activeTeachers()
                : null,
            'classes' => $canSeeClasses ? $this->school->activeClasses($academicYearId) : null,
            'classCapacity' => $canSeeClasses ? $this->school->classCapacityUsage($academicYearId) : null,
            'guardians' => $this->allows('GuardianProfile', 'guardian.view')
                ? $this->school->activeGuardians()
                : null,
            'classrooms' => $this->allows('Classroom', 'classroom.view')
                ? $this->school->activeClassrooms()
                : null,
        ];
    }

    /**
     * Today's attendance plus a short trend.
     *
     * `taken` is false when no class has recorded attendance yet today; the
     * page shows an empty state in that case instead of reporting 0% present.
     *
     * @return array<string, mixed>|null
     */
    private function attendance(?int $academicYearId): ?array
    {
        if (! $this->allows('Attendance', 'attendance.view')) {
            return null;
        }

        return [
            'today' => $this->attendance->forDate($academicYearId),
            'trend' => $this->attendance->dailyTrend($academicYearId),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function finance(?int $academicYearId): ?array
    {
        if (! $this->allows('Finance', 'invoice.view')) {
            return null;
        }

        return [
            'hasInvoices' => $this->finance->hasInvoices($academicYearId),
            'outstanding' => $this->finance->outstanding($academicYearId),
            'overdue' => $this->finance->overdue($academicYearId),
            'collectedThisMonth' => $this->finance->collectedThisMonth($academicYearId),
            'collectedToday' => $this->finance->collectedToday($academicYearId),
            'awaitingConfirmation' => $this->finance->awaitingConfirmation($academicYearId),
            'monthlyCollection' => $this->finance->monthlyCollection($academicYearId),
        ];
    }

    /**
     * Conditions worth acting on. Only genuinely non-zero conditions are
     * returned, so the panel is empty when nothing needs attention rather
     * than listing a column of zeroes.
     *
     * Each item carries a resolved URL to the existing page that resolves it,
     * so the dashboard adds no routes of its own.
     *
     * @param  array<string, mixed>|null  $finance
     * @return array<int, array{key: string, count: int, severity: string, url: string|null}>
     */
    private function attention(?int $academicYearId, ?array $finance): array
    {
        $items = [];

        if ($finance !== null && $finance['overdue']['invoices'] > 0) {
            $items[] = [
                'key' => 'overdueInvoices',
                'count' => $finance['overdue']['invoices'],
                'severity' => 'error',
                'url' => $this->urlFor('invoices.index'),
            ];
        }

        if ($finance !== null
            && $finance['awaitingConfirmation']['count'] > 0
            && $this->allows('Finance', 'payment.view')) {
            $items[] = [
                'key' => 'pendingPayments',
                'count' => $finance['awaitingConfirmation']['count'],
                'severity' => 'warning',
                'url' => $this->urlFor('payments.index'),
            ];
        }

        if ($this->allows('Student', 'student.view')) {
            $unassigned = $this->school->studentsWithoutClass($academicYearId);

            if ($unassigned > 0) {
                $items[] = [
                    'key' => 'studentsWithoutClass',
                    'count' => $unassigned,
                    'severity' => 'warning',
                    'url' => $this->urlFor('students.index'),
                ];
            }
        }

        if ($this->allows('SchoolClass', 'school-class.view')) {
            $emptyClasses = $this->school->classesWithoutStudents($academicYearId);

            if ($emptyClasses > 0) {
                $items[] = [
                    'key' => 'classesWithoutStudents',
                    'count' => $emptyClasses,
                    'severity' => 'info',
                    'url' => $this->urlFor('classes.index'),
                ];
            }
        }

        return $items;
    }

    /** Null when the module owning the page is disabled, so the item renders unlinked. */
    private function urlFor(string $name): ?string
    {
        return Route::has($name) ? route($name) : null;
    }

    private function allows(string $module, string $permission): bool
    {
        return $this->modules->isEnabled($module)
            && (bool) auth()->user()?->can($permission);
    }
}
