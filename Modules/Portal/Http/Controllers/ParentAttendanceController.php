<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Attendance\Actions\BuildStudentAttendanceHistoryAction;
use Modules\Attendance\DTOs\StudentAttendanceFilterDTO;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Services\PortalScopeService;
use Modules\Portal\Traits\ResolvesGuardianChildren;

class ParentAttendanceController extends BaseInertiaController
{
    use ResolvesGuardianChildren;

    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request, BuildStudentAttendanceHistoryAction $action): Response
    {
        $child = $this->selectedChild($request);

        return $this->render('Portal::Parent/Attendance', [
            'history' => $child ? $this->serialize($action->execute(
                StudentAttendanceFilterDTO::fromRequest($request, $child->id),
            )) : null,
            'children' => $this->childOptions(),
            'selectedChild' => $child?->id,
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
