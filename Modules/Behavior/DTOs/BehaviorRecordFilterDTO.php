<?php

namespace Modules\Behavior\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

/**
 * No `search` field: behavior records don't have a natural free-text field
 * worth searching (student/category are picked via structured filters
 * instead), so only structured filters are offered here.
 */
final readonly class BehaviorRecordFilterDTO
{
    public function __construct(
        public ?int $studentId,
        public ?string $status,
        public ?string $type,
        public ?string $dateFrom,
        public ?string $dateTo,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
            status: $request->string('status')->toString() ?: null,
            type: $request->string('type')->toString() ?: null,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'date')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
