<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Attendance\Models\AttendanceRecord;

/**
 * Today's attendance for the admin dashboard.
 *
 * Reads the denormalised per-session counters already maintained on
 * attendance_records rather than re-deriving them from attendance_details, so
 * the dashboard agrees with the attendance module by construction.
 *
 * Absence of a record means "not taken yet", never "everyone was absent" —
 * callers get null in that case so the page can show an honest empty state.
 */
class DashboardAttendanceQuery
{
    /**
     * Aggregated attendance for a single day.
     *
     * @return array{taken: bool, present: int, absent: int, late: int, excused: int, total: int, rate: float|null, classesTaken: int}
     */
    public function forDate(?int $academicYearId, ?string $date = null): array
    {
        $date ??= now()->toDateString();

        $totals = $this->recordsFor($academicYearId)
            ->whereDate('date', $date)
            ->selectRaw('COUNT(*) as classes_taken')
            ->selectRaw('COALESCE(SUM(present_count), 0) as present')
            ->selectRaw('COALESCE(SUM(absent_count), 0) as absent')
            ->selectRaw('COALESCE(SUM(late_count), 0) as late')
            ->selectRaw('COALESCE(SUM(excused_count), 0) as excused')
            ->first();

        $classesTaken = (int) ($totals?->classes_taken ?? 0);

        $present = (int) ($totals?->present ?? 0);
        $absent = (int) ($totals?->absent ?? 0);
        $late = (int) ($totals?->late ?? 0);
        $excused = (int) ($totals?->excused ?? 0);
        $total = $present + $absent + $late + $excused;

        return [
            'taken' => $classesTaken > 0,
            'classesTaken' => $classesTaken,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'total' => $total,
            /*
             * Late students are physically present, so they count toward the
             * attendance rate; excused absences stay in the denominator
             * because they are still a day the student did not attend.
             */
            'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
        ];
    }

    /**
     * Daily attendance rate over the last $days days, oldest first.
     *
     * Days with no record are omitted entirely rather than plotted as zero,
     * which would draw a misleading crash in the chart for a weekend.
     *
     * @return array<int, array{date: string, rate: float, present: int, total: int}>
     */
    public function dailyTrend(?int $academicYearId, int $days = 14): array
    {
        return $this->recordsFor($academicYearId)
            ->where('date', '>=', now()->subDays($days - 1)->toDateString())
            ->where('date', '<=', now()->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->selectRaw('date')
            ->selectRaw('COALESCE(SUM(present_count), 0) as present')
            ->selectRaw('COALESCE(SUM(late_count), 0) as late')
            ->selectRaw('COALESCE(SUM(absent_count), 0) as absent')
            ->selectRaw('COALESCE(SUM(excused_count), 0) as excused')
            ->get()
            ->map(function (object $row): ?array {
                $present = (int) $row->present;
                $late = (int) $row->late;
                $total = $present + $late + (int) $row->absent + (int) $row->excused;

                if ($total === 0) {
                    return null;
                }

                return [
                    'date' => (string) $row->date,
                    'present' => $present,
                    'total' => $total,
                    'rate' => round((($present + $late) / $total) * 100, 1),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return Builder<AttendanceRecord>
     */
    private function recordsFor(?int $academicYearId): Builder
    {
        return AttendanceRecord::query()
            ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId));
    }
}
