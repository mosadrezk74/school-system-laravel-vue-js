<?php

namespace Modules\Promotion\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class PromotionFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public ?int $fromAcademicYearId,
        public ?int $toAcademicYearId,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
            fromAcademicYearId: $request->filled('from_academic_year_id')
                ? $request->integer('from_academic_year_id')
                : null,
            toAcademicYearId: $request->filled('to_academic_year_id')
                ? $request->integer('to_academic_year_id')
                : null,
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
