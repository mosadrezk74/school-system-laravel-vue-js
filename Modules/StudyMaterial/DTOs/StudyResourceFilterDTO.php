<?php

namespace Modules\StudyMaterial\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class StudyResourceFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $subjectId,
        public ?int $gradeLevelId,
        public ?int $teacherId,
        public ?string $type,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            subjectId: $request->filled('subject_id') ? (int) $request->input('subject_id') : null,
            gradeLevelId: $request->filled('grade_level_id') ? (int) $request->input('grade_level_id') : null,
            teacherId: $request->filled('teacher_id') ? (int) $request->input('teacher_id') : null,
            type: $request->string('type')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
