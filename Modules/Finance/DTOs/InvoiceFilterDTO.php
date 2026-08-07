<?php

namespace Modules\Finance\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class InvoiceFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $studentId,
        public ?string $status,
        public ?string $issuedFrom,
        public ?string $issuedTo,
        public ?bool $overdueOnly,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
            status: $request->string('status')->toString() ?: null,
            issuedFrom: $request->string('issued_from')->toString() ?: null,
            issuedTo: $request->string('issued_to')->toString() ?: null,
            overdueOnly: $request->has('overdue_only') ? $request->boolean('overdue_only') : null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
