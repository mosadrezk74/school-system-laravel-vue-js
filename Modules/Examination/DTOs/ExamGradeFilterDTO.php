<?php

namespace Modules\Examination\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class ExamGradeFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $gradeLevelId,
        public ?int $subjectId,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            gradeLevelId: $request->filled('grade_level_id') ? (int) $request->input('grade_level_id') : null,
            subjectId: $request->filled('subject_id') ? (int) $request->input('subject_id') : null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
