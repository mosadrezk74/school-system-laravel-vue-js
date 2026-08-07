<?php

namespace Modules\Portal\Actions;

use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\DashboardWidgetCollector;
use Modules\Core\Support\DashboardWidget;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Meeting\Models\Meeting;
use Modules\Portal\Services\PortalScopeService;

class BuildParentDashboardAction extends BaseAction
{
    public function __construct(
        protected PortalScopeService $scope,
        protected DashboardWidgetCollector $collector,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(mixed $input = null): array
    {
        if (! $this->scope->hasChildren()) {
            return [];
        }

        return $this->collector->collect($this->widgets());
    }

    /**
     * @return array<int, DashboardWidget>
     */
    private function widgets(): array
    {
        return [
            DashboardWidget::make('children', fn (): int => count($this->childIds()))
                ->module('Student')
                ->icon('bx-group')
                ->route('/parent/children')
                ->priority(10),

            DashboardWidget::make('attendance_rate', fn (): float => $this->attendanceRate())
                ->module('Attendance')
                ->permission('attendance.view')
                ->icon('bx-check-square')
                ->color('success')
                ->route('/parent/attendance')
                ->priority(20),

            DashboardWidget::make('pending_homework', fn (): int => $this->pendingHomework())
                ->module('Assignment')
                ->permission('assignment.view')
                ->icon('bx-task')
                ->color('warning')
                ->route('/parent/homework')
                ->priority(30),

            DashboardWidget::make('outstanding_fees', fn (): float => $this->outstandingFees())
                ->module('Finance')
                ->permission('invoice.view')
                ->icon('bx-wallet')
                ->color('error')
                ->route('/parent/fees')
                ->priority(40),

            DashboardWidget::make('upcoming_meetings', fn (): int => $this->upcomingMeetings())
                ->module('Meeting')
                ->permission('meeting.view')
                ->icon('bx-video')
                ->route('/parent/meetings')
                ->priority(50),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function childIds(): array
    {
        return $this->scope->guardianChildIds();
    }

    private function attendanceRate(): float
    {
        $counts = AttendanceDetail::query()
            ->whereIn('student_id', $this->childIds())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $all = (int) $counts->sum();

        if ($all === 0) {
            return 0.0;
        }

        $present = (int) ($counts[AttendanceStatus::Present->value] ?? 0)
            + (int) ($counts[AttendanceStatus::Late->value] ?? 0);

        return round(($present / $all) * 100, 1);
    }

    private function pendingHomework(): int
    {
        $classIds = $this->scope->accessibleClassIds() ?? [];

        if ($classIds === []) {
            return 0;
        }

        $submitted = AssignmentSubmission::query()
            ->whereIn('student_id', $this->childIds())
            ->select('assignment_id');

        return Assignment::query()
            ->whereNotNull('published_at')
            ->whereIn('class_id', $classIds)
            ->whereNotIn('id', $submitted)
            ->count();
    }

    private function outstandingFees(): float
    {
        return round((float) Invoice::query()
            ->whereIn('student_id', $this->childIds())
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value])
            ->sum('balance_due'), 2);
    }

    private function upcomingMeetings(): int
    {
        $classIds = $this->scope->accessibleClassIds() ?? [];

        if ($classIds === []) {
            return 0;
        }

        return Meeting::query()
            ->whereIn('school_class_id', $classIds)
            ->where('scheduled_at', '>=', now())
            ->count();
    }
}
