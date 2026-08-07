<?php

namespace Modules\Portal\Actions;

use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\DashboardWidgetCollector;
use Modules\Core\Support\DashboardWidget;
use Modules\Meeting\Models\Meeting;
use Modules\Portal\Services\PortalScopeService;
use Modules\Timetable\Actions\GetTeacherScheduleAction;

class BuildTeacherDashboardAction extends BaseAction
{
    public function __construct(
        protected PortalScopeService $scope,
        protected GetTeacherScheduleAction $schedule,
        protected DashboardWidgetCollector $collector,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(mixed $input = null): array
    {
        if ($this->scope->teacherUserId() === null) {
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
            DashboardWidget::make('today_classes', fn (): int => $this->schedule
                ->execute(['dayOfWeek' => (int) now()->dayOfWeek])->count())
                ->module('Timetable')
                ->icon('bx-calendar-week')
                ->route('/teacher/schedule')
                ->priority(10),

            DashboardWidget::make('attendance_pending', fn (): int => $this->attendancePending())
                ->module('Attendance')
                ->permission('attendance.create')
                ->icon('bx-list-check')
                ->color('warning')
                ->route('/teacher/attendance')
                ->priority(20),

            DashboardWidget::make('homework_to_review', fn (): int => $this->homeworkToReview())
                ->module('Assignment')
                ->permission('assignment.view')
                ->icon('bx-task')
                ->color('info')
                ->route('/teacher/homework')
                ->priority(30),

            DashboardWidget::make('my_classes', fn (): int => count($this->scope->teacherScheduleClassIds()))
                ->icon('bx-chalkboard')
                ->color('success')
                ->route('/teacher/classes')
                ->priority(40),

            DashboardWidget::make('my_students', fn (): int => $this->scope->teacherStudents()->count())
                ->module('Student')
                ->icon('bx-user')
                ->color('secondary')
                ->route('/teacher/classes')
                ->priority(50),

            DashboardWidget::make('upcoming_meetings', fn (): int => $this->upcomingMeetings())
                ->module('Meeting')
                ->permission('meeting.view')
                ->icon('bx-video')
                ->route('/teacher/meetings')
                ->priority(60),
        ];
    }

    private function attendancePending(): int
    {
        $classIds = $this->scope->teacherScheduleClassIds();

        if ($classIds === []) {
            return 0;
        }

        $taken = AttendanceRecord::query()
            ->whereIn('class_id', $classIds)
            ->whereDate('date', now()->toDateString())
            ->distinct()
            ->count('class_id');

        return max(0, count($classIds) - $taken);
    }

    private function homeworkToReview(): int
    {
        return AssignmentSubmission::query()
            ->where('is_graded', false)
            ->whereIn('assignment_id', Assignment::query()
                ->where('teacher_id', $this->scope->teacherUserId())
                ->select('id'))
            ->count();
    }

    private function upcomingMeetings(): int
    {
        return Meeting::query()
            ->where('teacher_id', $this->scope->teacherUserId())
            ->where('scheduled_at', '>=', now())
            ->count();
    }
}
