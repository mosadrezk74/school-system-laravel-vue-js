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
        abort_unless($this->scope->canAccessClass((int) $request->validated('class_id')), 403);

        $action->execute(MarkAttendanceDTO::fromRequest($request));

        return $this->backWithSuccess(__('attendance::attendance.marked'));
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
