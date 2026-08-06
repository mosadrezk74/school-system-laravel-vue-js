<?php

namespace Modules\Attendance\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Attendance\DTOs\AttendanceDashboardFilterDTO;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class BuildAttendanceDashboardAction extends BaseAction
{
    public function __construct(
        protected AttendanceRepositoryInterface $repository,
    ) {}

    /**
     * @param  AttendanceDashboardFilterDTO  $input
     * @return array<string, mixed>
     */
    public function execute(mixed $input): array
    {
        /** @var AttendanceDashboardFilterDTO $filter */
        $filter = $input;

        $today = Carbon::today();

        $todayCounts = $this->repository->statusCountsBetween($filter, $today->toDateString(), $today->toDateString());
        $weekCounts = $this->repository->statusCountsBetween($filter, $today->copy()->startOfWeek()->toDateString(), $today->toDateString());
        $monthCounts = $this->repository->statusCountsBetween($filter, $today->copy()->startOfMonth()->toDateString(), $today->toDateString());

        $trendFrom = $today->copy()->subDays($filter->trendDays - 1);
        $daily = $this->repository->dailyStatusTotals($filter, $trendFrom->toDateString(), $today->toDateString());

        return [
            'today' => [
                'attendance_percentage' => $this->percentage($todayCounts, AttendanceStatus::Present->value),
                'present_count' => $todayCounts[AttendanceStatus::Present->value] ?? 0,
                'absent_count' => $todayCounts[AttendanceStatus::Absent->value] ?? 0,
                'late_count' => $todayCounts[AttendanceStatus::Late->value] ?? 0,
                'excused_count' => $todayCounts[AttendanceStatus::Excused->value] ?? 0,
                'other_count' => $todayCounts[AttendanceStatus::Other->value] ?? 0,
            ],
            'week_attendance_percentage' => $this->percentage($weekCounts, AttendanceStatus::Present->value),
            'month_attendance_percentage' => $this->percentage($monthCounts, AttendanceStatus::Present->value),
            'trend' => $this->trend($daily),
            'distribution' => $this->distribution($monthCounts),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     */
    protected function percentage(array $counts, string $status): float
    {
        $total = array_sum($counts);

        return $total > 0 ? round(($counts[$status] ?? 0) / $total * 100, 2) : 0.0;
    }

    /**
     * @param  Collection<int, object>  $daily
     * @return array<int, array{date: string, attendance_percentage: float}>
     */
    protected function trend($daily): array
    {
        return $daily
            ->map(fn ($row): array => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'attendance_percentage' => (int) $row->total > 0
                    ? round((int) $row->present / (int) $row->total * 100, 2)
                    : 0.0,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array{status: string, label: string, total: int}>
     */
    protected function distribution(array $counts): array
    {
        return array_map(
            fn (AttendanceStatus $status): array => [
                'status' => $status->value,
                'label' => $status->label(),
                'total' => $counts[$status->value] ?? 0,
            ],
            AttendanceStatus::cases(),
        );
    }
}
