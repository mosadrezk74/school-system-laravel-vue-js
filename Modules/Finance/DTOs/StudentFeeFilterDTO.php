<?php

namespace Modules\Finance\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class StudentFeeFilterDTO
{
    public function __construct(
        public ?int $studentId,
        public ?int $feeStructureId,
        public ?string $status,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
            feeStructureId: $request->filled('fee_structure_id') ? (int) $request->input('fee_structure_id') : null,
            status: $request->string('status')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
