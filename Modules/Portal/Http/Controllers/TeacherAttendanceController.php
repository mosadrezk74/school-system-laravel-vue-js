<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Attendance\Actions\MarkAttendanceAction;
use Modules\Attendance\DTOs\MarkAttendanceDTO;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Http\Requests\StoreAttendanceRequest;
use Modules\Attendance\Services\AttendanceRosterService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

class TeacherAttendanceController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request, AttendanceRosterService $roster): Response
    {
        $classId = $request->filled('class_id') ? (int) $request->input('class_id') : null;
        $date = $request->filled('date') ? $request->date('date')->toDateString() : now()->toDateString();

        if (! $this->scope->canAccessClass($classId)) {
            $classId = null;
        }

        return $this->render('Portal::Teacher/Attendance', [
            'classes' => $this->myClasses(),
            'statuses' => AttendanceStatus::options(),
            'filters' => ['class_id' => $classId, 'date' => $date],
            'roster' => $classId ? $roster->forClassAndDate($classId, $date) : [],
        ]);
    }

    public function store(StoreAttendanceRequest $request, MarkAttendanceAction $action): RedirectResponse
    {
        $classId = (int) $request->validated('class_id');
        $subjectId = $request->validated('subject_id');

        abort_unless($this->scope->canAccessClass($classId), 403);

        if ($subjectId !== null) {
            abort_unless($this->scope->canAccessClassSubject($classId, (int) $subjectId), 403);
        }

        $this->assertStudentsBelongToClass($request->validated('students'), $classId);

        $action->execute(MarkAttendanceDTO::fromRequest($request));

        return $this->backWithSuccess(__('attendance::attendance.marked'));
    }

    /**
     * @param  array<int, array{student_id: int}>  $students
     */
    private function assertStudentsBelongToClass(array $students, int $classId): void
    {
        $submitted = collect($students)->pluck('student_id')->map(fn ($id): int => (int) $id)->unique();

        $enrolled = StudentProfile::query()
            ->where('current_class_id', $classId)
            ->whereIn('id', $submitted)
            ->pluck('id');

        abort_unless($submitted->count() === $enrolled->count(), 403);
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function myClasses(): array
    {
        return $this->scope->teacherClasses()
            ->map(fn (SchoolClass $class): array => [
                'id' => $class->id,
                'name' => $class->name,
            ])
            ->values()
            ->all();
    }
}
