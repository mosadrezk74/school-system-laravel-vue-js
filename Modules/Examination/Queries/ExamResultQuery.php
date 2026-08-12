<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Examination\Models\ExamResult;
use Modules\Portal\Services\PortalScopeService;

/**
 * Reading exam results.
 *
 * The publish rule lives here rather than in each portal controller: a result
 * belongs to an exam, and an unpublished exam is one the school has not
 * released yet, so families must not see its marks. Centralising it means a
 * new screen cannot forget the condition — which is exactly how the student
 * and guardian grade pages came to leak unpublished marks.
 *
 * Staff (admin/teacher) deliberately keep their existing unrestricted view;
 * entering and reviewing marks before publishing is the point of the workflow.
 */
class ExamResultQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['obtained_marks', 'percentage', 'rank', 'created_at'];

    protected function baseQuery(): Builder
    {
        return ExamResult::query();
    }

    /**
     * Restricts to results whose parent exam has been published.
     *
     * whereHas rather than a join so the condition composes with any other
     * clause and cannot duplicate rows.
     */
    public function publishedOnly(): self
    {
        $this->query->whereHas('exam', fn (Builder $query) => $query->where('is_published', true));

        return $this;
    }

    /**
     * Applies the portal's ownership scope — a student sees only their own
     * profile id, a guardian only their children's, a teacher only the
     * students in classes they teach, staff everything.
     */
    public function forPortalUser(PortalScopeService $scope): self
    {
        $scope->applyStudentScope($this->query);

        return $this;
    }

    /**
     * The ownership scope and the publish rule together — what every
     * student-facing and guardian-facing read must use.
     */
    public function visibleToFamily(PortalScopeService $scope): self
    {
        return $this->forPortalUser($scope)->publishedOnly();
    }

    /**
     * Narrows to one student. The id must already have been checked against
     * the caller's scope; this only filters, it does not authorise.
     */
    public function forStudent(?int $studentId): self
    {
        if ($studentId !== null) {
            $this->query->where('student_id', $studentId);
        }

        return $this;
    }

    public function forSubject(?int $subjectId): self
    {
        if ($subjectId !== null) {
            $this->query->where('subject_id', $subjectId);
        }

        return $this;
    }

    public function latestFirst(): self
    {
        $this->query->latest('id');

        return $this;
    }
}
