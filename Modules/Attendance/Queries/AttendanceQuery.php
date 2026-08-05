<?php

namespace Modules\Attendance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Core\Abstracts\BaseQuery;

class AttendanceQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['class_id', 'subject_id', 'academic_year_id', 'semester_id', 'session'];

    /** @var array<int, string> */
    protected array $sortableFields = ['date', 'session', 'created_at'];

    protected function baseQuery(): Builder
    {
        return AttendanceRecord::query()->with(['class', 'subject', 'academicYear', 'semester']);
    }

    public function classId(?int $classId): self
    {
        if ($classId) {
            $this->query->where('class_id', $classId);
        }

        return $this;
    }

    public function subjectId(?int $subjectId): self
    {
        if ($subjectId) {
            $this->query->where('subject_id', $subjectId);
        }

        return $this;
    }

    public function academicYearId(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->where('academic_year_id', $academicYearId);
        }

        return $this;
    }

    public function semesterId(?int $semesterId): self
    {
        if ($semesterId) {
            $this->query->where('semester_id', $semesterId);
        }

        return $this;
    }

    public function session(?int $session): self
    {
        if ($session) {
            $this->query->where('session', $session);
        }

        return $this;
    }

    public function dateBetween(?string $from, ?string $to): self
    {
        if ($from) {
            $this->query->whereDate('date', '>=', $from);
        }

        if ($to) {
            $this->query->whereDate('date', '<=', $to);
        }

        return $this;
    }
}
