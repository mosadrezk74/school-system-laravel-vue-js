<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Attendance\Actions\BuildStudentAttendanceHistoryAction;
use Modules\Attendance\DTOs\StudentAttendanceFilterDTO;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Services\PortalScopeService;

class StudentAttendanceController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request, BuildStudentAttendanceHistoryAction $action): Response
    {
        $student = $this->scope->currentStudentProfile();

        abort_if($student === null, 403);

        $request->merge(['student_id' => $student->id]);

        return $this->render('Portal::Student/Attendance', [
            'history' => $this->serialize($action->execute(StudentAttendanceFilterDTO::fromRequest($request, $student->id))),
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $history
     * @return array<string, mixed>
     */
    private function serialize(array $history): array
    {
        return [
            'summary' => $history['summary']->toArray(),
            'timeline' => $history['timeline'],
        ];
    }
}
