<?php

namespace Modules\Portal\Actions;

use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\DashboardWidgetCollector;
use Modules\Core\Support\DashboardWidget;
use Modules\Examination\Models\ExamResult;
use Modules\Meeting\Models\Meeting;
use Modules\Portal\Services\PortalScopeService;

class BuildStudentDashboardAction extends BaseAction
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
        if ($this->scope->currentStudentProfile() === null) {
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
            DashboardWidget::make('pending_homework', fn (): int => $this->pendingHomework())
                ->module('Assignment')
                ->permission('assignment.view')
                ->icon('bx-task')
                ->color('warning')
                ->route('/student/homework')
                ->priority(10),

            DashboardWidget::make('attendance_rate', fn (): float => $this->attendanceRate())
                ->module('Attendance')
                ->permission('attendance.view')
                ->icon('bx-check-square')
                ->color('success')
                ->route('/student/attendance')
                ->priority(20),

            DashboardWidget::make('recent_results', fn (): int => $this->recentResults())
                ->module('Examination')
                ->permission('exam-result.view')
                ->icon('bx-medal')
                ->color('info')
                ->route('/student/grades')
                ->priority(30),

            DashboardWidget::make('upcoming_meetings', fn (): int => $this->upcomingMeetings())
                ->module('Meeting')
                ->permission('meeting.view')
                ->icon('bx-video')
                ->route('/student/meetings')
                ->priority(40),

            DashboardWidget::make('unread_notifications', fn (): int => $this->unreadNotifications())
                ->module('Notification')
                ->icon('bx-bell')
                ->color('error')
                ->route('/notifications')
                ->priority(60),
        ];
    }

    private function studentId(): int
    {
        return (int) $this->scope->currentStudentProfile()?->id;
    }

    private function pendingHomework(): int
    {
        $submitted = AssignmentSubmission::query()
            ->where('student_id', $this->studentId())
            ->select('assignment_id');

        return Assignment::query()
            ->whereNotNull('published_at')
            ->whereIn('class_id', $this->scope->accessibleClassIds() ?? [])
            ->whereNotIn('id', $submitted)
            ->count();
    }

    private function attendanceRate(): float
    {
        $counts = AttendanceDetail::query()
            ->where('student_id', $this->studentId())
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

    private function recentResults(): int
    {
        return ExamResult::query()
            ->where('student_id', $this->studentId())
            ->count();
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

    private function unreadNotifications(): int
    {
        return (int) auth()->user()?->unreadNotifications()->count();
    }
}
