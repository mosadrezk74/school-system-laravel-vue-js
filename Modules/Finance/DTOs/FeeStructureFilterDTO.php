<?php

namespace Modules\Finance\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class FeeStructureFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $feeCategoryId,
        public ?int $gradeLevelId,
        public ?int $classroomId,
        public ?string $frequency,
        public ?bool $isOptional,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            feeCategoryId: $request->filled('fee_category_id') ? (int) $request->input('fee_category_id') : null,
            gradeLevelId: $request->filled('grade_level_id') ? (int) $request->input('grade_level_id') : null,
            classroomId: $request->filled('classroom_id') ? (int) $request->input('classroom_id') : null,
            frequency: $request->string('frequency')->toString() ?: null,
            isOptional: $request->has('is_optional') ? $request->boolean('is_optional') : null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
