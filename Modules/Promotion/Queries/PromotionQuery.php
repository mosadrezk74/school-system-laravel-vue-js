<?php

namespace Modules\Promotion\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Promotion\Enums\PromotionStatus;
use Modules\Promotion\Models\StudentPromotion;

class PromotionQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['promoted_at', 'status', 'created_at'];

    protected function baseQuery(): Builder
    {
        return StudentPromotion::query()->with([
            'student.user',
            'fromAcademicYear',
            'toAcademicYear',
            'toSemester',
            'fromClass',
            'toClass',
            'promotedBy',
        ]);
    }

    /** Student names live on users, so the search reaches through the profile. */
    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->whereHas('student.user', fn (Builder $query) => $query->where('name', 'LIKE', "%{$term}%"));

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status !== null && PromotionStatus::tryFrom($status) instanceof PromotionStatus) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function fromAcademicYear(?int $academicYearId): self
    {
        if ($academicYearId !== null) {
            $this->query->where('from_academic_year_id', $academicYearId);
        }

        return $this;
    }

    public function toAcademicYear(?int $academicYearId): self
    {
        if ($academicYearId !== null) {
            $this->query->where('to_academic_year_id', $academicYearId);
        }

        return $this;
    }

    public function forStudent(?int $studentId): self
    {
        if ($studentId !== null) {
            $this->query->where('student_id', $studentId);
        }

        return $this;
    }

    public function latestFirst(): self
    {
        $this->query->orderByDesc('promoted_at')->orderByDesc('id');

        return $this;
    }
}
